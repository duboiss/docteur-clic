<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentAdminType;
use App\Form\AppointmentDoctorType;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use App\Service\AppointmentService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/appointment')]
#[IsGranted('ROLE_USER')]
class AppointmentController extends AbstractController
{
    #[Route('/last_month', name: 'app_appointment_last_month', methods: ['GET'])]
    public function last_month(AppointmentRepository $appointmentRepository, SerializerInterface $serializer): Response
    {
        $appointments = $appointmentRepository->findLastMonthAppointments();
        $lastMonthAppointments = $serializer->serialize($appointments, 'json', [AbstractNormalizer::GROUPS => ['appointment']]);

        return new JsonResponse($lastMonthAppointments, 200, [], true);
    }

    #[IsGranted('ROLE_DOCTOR')]
    #[Route('/doctor', name: 'app_doctor_appointment_index', methods: ['GET', 'POST'])]
    public function doctor_create(Request $request, EntityManagerInterface $entityManager, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentDoctorType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getUser();
            if ($appointmentRepository->findBy(['doctor' => $user, 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError('Vous avez déjà un patient sur ce créneau !'));

                return $this->render('appointment/doctor_create.html.twig', [
                    'form' => $form,
                ]);
            }

            if ($alreadyExistingAppointment = $appointmentRepository->findOneBy(['patient' => $appointment->getPatient(), 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError(sprintf(
                    'Le patient a déjà un rendez-vous avec le docteur %s sur ce créneau',
                    $alreadyExistingAppointment->getDoctor()->getName()
                )));

                return $this->render('appointment/doctor_create.html.twig', ['form' => $form]);
            }

            $appointment->setDoctor($user);
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Rendez-vous enregistré !');

            return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/doctor_create.html.twig', [
            'form' => $form,
        ]);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/admin', name: 'app_admin_appointment_index', methods: ['GET', 'POST'])]
    public function admin_create(Request $request, EntityManagerInterface $entityManager, AppointmentRepository $appointmentRepository): Response
    {
        $appointment = new Appointment();
        $form = $this->createForm(AppointmentAdminType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($appointmentRepository->findBy(['doctor' => $appointment->getDoctor(), 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError('Le docteur a déjà un patient sur ce créneau !'));

                return $this->render('appointment/admin_create.html.twig', ['form' => $form]);
            }

            if ($alreadyExistingAppointment = $appointmentRepository->findOneBy(['patient' => $appointment->getPatient(), 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError(sprintf(
                    'Le patient a déjà un rendez-vous avec le docteur %s sur ce créneau',
                    $alreadyExistingAppointment->getDoctor()->getName()
                )));

                return $this->render('appointment/admin_create.html.twig', ['form' => $form]);
            }

            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Rendez-vous enregistré !');

            return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/admin_create.html.twig', [
            'form' => $form,
        ]);
    }

    /**
     * @throws \JsonException
     */
    #[Route('/{id}', name: 'app_appointment_index', methods: ['GET', 'POST'])]
    public function create(User $doctor, Request $request, EntityManagerInterface $entityManager, AppointmentRepository $appointmentRepository, AppointmentService $appointmentService): Response
    {
        if (!$doctor->isDoctor()) {
            $this->addFlash('danger', 'Vous ne pouvez prendre rendez-vous qu\'avec un docteur !');

            return $this->redirectToRoute('app_doctors', [], Response::HTTP_SEE_OTHER);
        }

        $doctorAppointments = $appointmentService->getFullCalendarDoctorAppointments($doctor);

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($doctor === $this->getUser()) {
                $form->addError(new FormError('Vous ne pouvez pas prendre rendez-vous avec vous-même !'));

                return $this->render('appointment/create.html.twig', [
                    'doctor' => $doctor,
                    'doctorAppointments' => $doctorAppointments,
                    'form' => $form,
                ]);
            }

            if ($appointmentRepository->findBy(['doctor' => $doctor, 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError('Ce docteur est déjà pris sur ce créneau !'));

                return $this->render('appointment/create.html.twig', [
                    'doctor' => $doctor,
                    'doctorAppointments' => $doctorAppointments,
                    'form' => $form,
                ]);
            }

            if ($alreadyExistingAppointment = $appointmentRepository->findOneBy(['patient' => $this->getUser(), 'startsAt' => $appointment->getStartsAt()])) {
                $form->addError(new FormError(sprintf(
                    'Vous avez déjà un rendez-vous avec le docteur %s sur ce créneau',
                    $alreadyExistingAppointment->getDoctor()->getName()
                )));

                return $this->render('appointment/create.html.twig', [
                    'doctor' => $doctor,
                    'doctorAppointments' => $doctorAppointments,
                    'form' => $form,
                ]);
            }

            $appointment->setDoctor($doctor);
            $appointment->setPatient($this->getUser());
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Rendez-vous enregistré !');

            return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/create.html.twig', [
            'doctor' => $doctor,
            'doctorAppointments' => json_encode($doctorAppointments, JSON_THROW_ON_ERROR),
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->getPayload()->get('_token'))) {
            $user = $this->getUser();

            if ($appointment->getDoctor() !== $user && $appointment->getPatient() !== $user) {
                $this->addFlash('danger', 'Vous ne pouvez pas annuler ce rendez-vous !');

                return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
            }

            if ($appointment->isPast()) {
                $this->addFlash('danger', "Impossible d'annuler ce rendez-vous, il est déjà passé");

                return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
            }
            $entityManager->remove($appointment);
            $entityManager->flush();
            $this->addFlash('info', 'Rendez-vous annulé');
        }

        return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
    }
}
