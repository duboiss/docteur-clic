<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Entity\User;
use App\Form\AppointmentType;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
    #[Route('/{id}', name: 'app_appointment_index', methods: ['GET', 'POST'])]
    public function create(User $doctor, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$doctor->isDoctor()) {
            $this->addFlash('danger', 'Vous ne pouvez prendre rendez-vous qu\'avec un docteur !');

            return $this->redirectToRoute('app_doctors', [], Response::HTTP_SEE_OTHER);
        }

        $appointment = new Appointment();
        $form = $this->createForm(AppointmentType::class, $appointment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $appointment->setDoctor($doctor);
            $appointment->setPatient($this->getUser());
            $mutableEndsAt = \DateTime::createFromImmutable($appointment->getStartsAt());
            $mutableEndsAt->modify('+1 hour');
            $appointment->setEndsAt(\DateTimeImmutable::createFromMutable($mutableEndsAt));
            $entityManager->persist($appointment);
            $entityManager->flush();

            $this->addFlash('success', 'Rendez-vous enregistré !');

            return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('appointment/create.html.twig', [
            'doctor' => $doctor,
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

    #[Route('/last_month', name: 'app_appointment_last_month', methods: ['GET'])]
    public function last_month(AppointmentRepository $appointmentRepository, SerializerInterface $serializer): Response
    {
        $appointments = $appointmentRepository->findLastMonthAppointments();
        $lastMonthAppointments = $serializer->serialize($appointments, 'json', [AbstractNormalizer::GROUPS => ['appointment']]);

        return new JsonResponse($lastMonthAppointments, 200, [], true);
    }
}
