<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/auth/login', name: 'app_login')]
    public function index(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
        if (!empty($user)) {
            return $this->redirect("/admin/member");
        }
        $error = $authenticationUtils->getLastAuthenticationError();

        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('login/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/auth/reg', name: 'app_login_reg')]
    public function register(UserPasswordHasherInterface $passwordHasher, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();

        $time = time();
        $user = new User();
        $user->setUsername("test");
        $user->setPassword($passwordHasher->hashPassword($user, "123456"));
        $user->setCreateAt($time);
        $user->setUpdateAt($time);
        $user->setDeleted(0);
        $user->setRoles(['ROLE_ADMIN']);

        $entityManager->persist($user);
        $entityManager->flush();


        return new Response(
            '<html><body>create username is : ' . $user->getUsername() . ' </body></html>'
        );
    }

}
