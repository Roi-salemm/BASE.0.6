<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\RegistrationFormType;
use App\Repository\UsersRepository;
use App\Security\UsersAuthenticator;
use App\Service\JWTService;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class RegistrationController extends AbstractController
{
    #[Route('/inscription', name: 'app_register')]
    public function register(Request $request, 
    UserPasswordHasherInterface $userPasswordHasher, 
    UserAuthenticatorInterface $userAuthenticator, 
    UsersAuthenticator $authenticator, 
    EntityManagerInterface $entityManager,
    SendMailService $mail,
    JWTService $jwt): Response
    {
        $user = new Users();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        // $nom = isset($_POST['nom']) ? $_POST['nom'] : null;
        // dd($nom);
        // dd($form['Nom']);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            // dd($form);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            // dd($user);

            $entityManager->persist($user);
            $entityManager->flush();
            //^^ do anything else you need here, like send an email

            //* génération du Json Web Token (JWT) de l'utilisateur 
            //? 1 cree le header
            $header=[
                'typ' => 'JWT',
                'alg' => 'HS256',
            ];

            //? 2 création du payload
            $payload=[
                'user_id' => $user->getId()
            ];

            //? 3 Génértations du token 
            $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

            
            //* Envoie du mail
            $mail->send(
                'no-reply@monsite.net',
                $user->getEmail(),
                'Activation de votre compte sur monsite@monsite.net',
                'register', //register.html.twig
                [
                    'user' => $user,
                    'token' => $token,
                ]
            );

            return $userAuthenticator->authenticateUser(
                $user,
                $authenticator,
                $request
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verif/{token}', name: 'verify_user')]
    public function verifyUser($token, JWTService $jwt, UsersRepository $usersRepository, EntityManagerInterface $em): Response 
    {
        // Verification de si le token est valide, n'a pas expiré et n'a pas etait modifier 
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('app.jwtsecret'))){
            //? Le token est valide donc on recupere le payload
            $payload = $jwt->getPayload($token);

            //? recuperaiton du user du token 
            $user = $usersRepository->find($payload['user_id']);

            // //? on verifie que l'utilisateur existe et n'a pas encore activer son compte 
            if($user && !$user->getIsVerified()){
                $user->setIsVerified(true);
                $em->flush($user);
                $this->addFlash('success', 'Votre compt utilisateur est validé');
                return $this->redirectToRoute('profile_index');
            }

        }
        // Ici un probléme dans le token 
        $this->addFlash('danger', 'le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
    

    #[Route('/renvoirverif', name: 'resend_verif')]
    public function resentVerif(JWTService $jwt, SendMailService $mail, UsersRepository $user): Response
    {
        $user = $this->getUser();

        if(!$user){
            $this->addFlash('danger', 'Vous devez être connecté pour acceder à cette page');
            return $this->redirectToRoute(('app_login'));
        }

        if($user->getIsVerified()){
            $this->addFlash('warning', 'Cet utilisateur est déjà activé');
            return $this->redirectToRoute('profile_index');
        }

        //* génération du Json Web Token (JWT) de l'utilisateur 
        //? 1 cree le header
        $header=[
            'typ' => 'JWT',
            'alg' => 'HS256',
        ];

        //? 2 création du payload
        $payload=[
            'user_id' => $user->getId()
        ];

        //? 3 Génértations du token 
        $token = $jwt->generate($header, $payload, $this->getParameter('app.jwtsecret'));

        
        //* Envoie du mail
        $mail->send(
            'no-reply@monsite.net',
            $user->getEmail(),
            'Activation de votre compte sur monsite@monsite.net',
            'register', //register.html.twig
            [
                'user' => $user,
                'token' => $token,
            ]
        );

        $this->addFlash('success', 'Email de vérification envoyé');
        return $this->redirectToRoute('profile_index');


    }


}
