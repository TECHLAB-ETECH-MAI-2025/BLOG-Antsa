<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AdminForm;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function index(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();
        $userCount = count($users);
        $verifiedCount = $userRepository->countUsersByIsVerified(true);
        $adminCount = $userRepository->countUsersByRole('ROLE_ADMIN');

        return $this->render('admin/index.html.twig', [
            'users' => $users,
            'userCount' => $userCount,
            'verifiedCount' => $verifiedCount,
            'adminCount' => $adminCount,
        ]);
    }

    #[Route('/admin/users', name: 'admin_user_index')]
    public function users(UserRepository $userRepository): Response
    {
        $users = $userRepository->findAll();

        return $this->render('admin/users.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/users/new', name: 'admin_user_new')]
    public function newUser(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $user = new User();
        $form = $this->createForm(AdminForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
            );
            $user->setCreatedAt(new \DateTimeImmutable());
            $user->setIsVerified(true);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur créé.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'admin_user_edit')]
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(AdminForm::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($plainPassword = $form->get('plainPassword')->getData()) {
                $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            }
            $em->flush();

            $this->addFlash('success', 'Utilisateur modifié.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user_form.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            if ($user === $this->getUser()) {
                $this->addFlash('error', 'Vous ne pouvez pas vous supprimer vous-même.');
                return $this->redirectToRoute('app_admin');
            }

            $em->remove($user);
            $em->flush();

            $this->addFlash('success', 'Utilisateur supprimé.');
        }

        return $this->redirectToRoute('app_admin');
    }
	
}