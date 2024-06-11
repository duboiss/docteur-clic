<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DoctorType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/doctor')]
#[IsGranted('ROLE_ADMIN')]
class DoctorController extends AbstractController
{
    #[Route('/', name: 'app_admin_doctor_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('doctor/index.html.twig', [
            'users' => $userRepository->findByRole('ROLE_DOCTOR'),
        ]);
    }

    #[Route('/new', name: 'app_admin_doctor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(DoctorType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $user->setRoles(['ROLE_DOCTOR']);
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash(
                'success',
                'Docteur créé !'
            );

            return $this->redirectToRoute('app_admin_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('doctor/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_doctor_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('doctor/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_doctor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if (!$user->isDoctor()) {
            return $this->redirectToRoute('app_admin_doctor_index', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(DoctorType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($userPasswordHasher->hashPassword($user, $form->get('plainPassword')->getData()));
            $entityManager->flush();
            $this->addFlash(
                'info',
                'Docteur mis à jour !'
            );

            return $this->redirectToRoute('app_admin_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('doctor/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_doctor_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if (!$user->isDoctor()) {
            return $this->redirectToRoute('app_admin_doctor_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();

            $this->addFlash(
                'success',
                'Docteur supprimé !'
            );
        }

        return $this->redirectToRoute('app_admin_doctor_index', [], Response::HTTP_SEE_OTHER);
    }
}
