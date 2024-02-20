<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\BrowserKit\Request;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    #[Route(path: '/connexion', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error
        ]);
    }


    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }


    #[Route(path: '/oubli-pass', name: 'forgotten_password')]
    public function forgottenPassword(
        HttpFoundationRequest $request, 
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $entityManagerInterface,
        SendMailService $mail
    ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $email = $form->get('email')->getData();
            $user = $usersRepository->findOneByEmail($email);

            if($user){
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
  
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
       
                $context = [
                    'url' => $url,
                    'user' => $user,
                ];

                $mail->send(
                    'no-replay@site.fr',
                    $user->getEmail(),
                    'Réinitialisation de mots de passe',
                    'password_reset',
                    $context
                );

                $this->addFlash('success', 'Email envoyé avec succées');
                return $this->redirectToRoute('app_login');

            }

            $this->addFlash('danger', 'un probléme est survenu');
            return $this->redirectToRoute('app_main');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView(),
        ]);
    }
    

    #[Route(path: '/oubli-pass/{token}', name: 'reset_pass')]
    public function resetPass(
        string $token,
        HttpFoundationRequest $request,
        UsersRepository $usersRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ): Response
    {
        $user = $usersRepository->findOneByResetToken($token);

        if($user){

            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                //? Effacer le token 
                $user->setResetToken('');
                //? va cherche le password 
                $user->setPassword(
                    $passwordHasher->hashPassword(          
                        $user,                              
                        $form->get('password')->getData()   
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('sucess', 'Mot de passe changer avec succèes');
                return $this->redirectToRoute('app_login');

            }

            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        $this->addFlash('danger','Jeton invalide');
        return $this->redirectToRoute('app_login');
    }

}
