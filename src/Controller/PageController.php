<?php

namespace App\Controller;

use App\Repository\AppointmentRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PageController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('page/home.html.twig');
    }

    #[Route('/doctors', name: 'app_doctors')]
    public function doctors(UserRepository $userRepository): Response
    {
        return $this->render('page/doctors.html.twig', [
            'doctors' => $userRepository->findByRole('ROLE_DOCTOR'),
        ]);
    }

    #[Route(path: '/account', name: 'app_account')]
    public function account(AppointmentRepository $appointmentRepository): Response
    {
        $user = $this->getUser();

        $templateData = [
            'nextPatientAppointments' => $appointmentRepository->findPatientFutureAppointments($user),
            'previousPatientAppointments' => $appointmentRepository->findPatientPastAppointments($user),
        ];

        if ($user->isDoctor()) {
            $templateData['nextDoctorAppointments'] = $appointmentRepository->findDoctorFutureAppointments($user);
            $templateData['previousDoctorAppointments'] = $appointmentRepository->findDoctorPastAppointments($user);
        }

        return $this->render('page/account.html.twig', $templateData);
    }
}
