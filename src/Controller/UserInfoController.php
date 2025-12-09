<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UserInfoController extends AbstractController
{
    #[Route('/userinfo', name: 'app_user_info')]
    #[IsGranted('ROLE_USER')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $user = $entityManager->getRepository(User::class)->findOneBy(['id' => $user->getId()]);

        return $this->render('user_info/index.html.twig', [
            'user' => $user,
            'user.contractCategory' => $user->getContractCategory(),
        ]);
    }
}
