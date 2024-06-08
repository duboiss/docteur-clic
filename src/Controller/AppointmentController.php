<?php

namespace App\Controller;

use App\Entity\Appointment;
use App\Repository\AppointmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/appointment')]
class AppointmentController extends AbstractController
{
    #[Route('/{id}', name: 'app_appointment_delete', methods: ['POST'])]
    public function delete(Request $request, Appointment $appointment, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$appointment->getId(), $request->getPayload()->get('_token'))) {
            $user = $this->getUser();

            if ($appointment->getDoctor() !== $user && $appointment->getPatient() !== $user) {
                $this->addFlash(
                    'danger',
                    'Vous ne pouvez pas annuler ce rendez-vous !'
                );

                return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
            }

            if ($appointment->isPast()) {
                $this->addFlash(
                    'danger',
                    "Impossible d'annuler ce rendez-vous, il est déjà passé"
                );

                return $this->redirectToRoute('app_account', [], Response::HTTP_SEE_OTHER);
            }
            $entityManager->remove($appointment);
            $entityManager->flush();
            $this->addFlash(
                'info',
                'Rendez-vous annulé'
            );
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
