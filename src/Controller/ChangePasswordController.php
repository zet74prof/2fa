<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ChangePasswordController extends AbstractController
{
    #[Route('/changepassword', name: 'app_change_password')]
    public function index(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        //if form is submitted
        if ($request->isMethod('POST')) {
            //get password from form
            $email = $request->request->get('email');
            $password = $request->request->get('newpassword');

            $user = $entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
            //hash password
            $hashedPassword = $hasher->hashPassword($user, $password);
            //set password
            $user->setPassword($hashedPassword);
            if ($user) {
                $entityManager->persist($user);
                $entityManager->flush();
            }
            //add flash message
            $this->addFlash('success', 'Mot de passe changé avec succès');
            //redirect to homepage
            return $this->redirectToRoute('app_change_password');
        }

        return $this->render('change_password/index.html.twig', [

        ]);
    }
}
