<?php

namespace App\Controller;

use App\Entity\Appointment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
