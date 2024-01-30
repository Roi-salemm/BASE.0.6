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

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
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
        //* "importe" le formuler definit dans ResetPasswordRequestFormType.php
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        //* pour traiter le formulire -> récuperé le data des imput
        $form->handleRequest($request);

        //& isSubmitted -> verrifit si le formulaire est envoyer 
        if($form->isSubmitted() && $form->isValid()){
            //? récuperation des infos de l'utilisateeur grace a son l'email 
            //? si il n'existe pas en base il renvoie null si non il renvoie les data client  
            $email = $form->get('email')->getData();
            $user = $usersRepository->findOneByEmail($email);
            // dd($user);

            //? Verrification de si il y a un utilisateur 
            //! Potentiel danger de sécu pour savoir si une addresse mail existe ou non.... 
            //TODO Voire du coté try Catch et securité 
            if($user){
                //* Générations d'un token de réinitialisation 
                $token = $tokenGeneratorInterface->generateToken();
                //* 
                $user->setResetToken($token);
                //* Ajoute en base le token
                $entityManagerInterface->persist($user);
                $entityManagerInterface->flush();

                //^^ génére un lien de réinitialisation du mots de pass
                $url = $this->generateUrl('reset_pass', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);
                //* Créations des données du mail 
                $context = [
                    'url' => $url,
                    'user' => $user,
                ];
                //* Envoie du mail 
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
            //? Dans le cas ou il n'y a pas d'utilisateur -> redirection vers main
            $this->addFlash('danger', 'un probléme est survenu');
            return $this->redirectToRoute('app_main');
        }

        //* "Crée" une vue du formulaire $form
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
        //* verifications de si on a ce token dans la BDD 
        $user = $usersRepository->findOneByResetToken($token);

        //* A t'on un user ? 
        if($user){

            $form = $this->createForm(ResetPasswordFormType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                //? Effacer le token 
                $user->setResetToken('');
                //? va cherche le password 
                $user->setPassword(
                    $passwordHasher->hashPassword(          // hache me mot de passe
                        $user,                              // de qui ?
                        $form->get('password')->getData()   // quelle mots de passe
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('sucess', 'Mot de passe changer avec succèes');
                return $this->redirectToRoute('app_login');

            }
            //* dans le cas ou le token ne correspond a aucun user
            return $this->render('security/reset_password.html.twig', [
                'passForm' => $form->createView()
            ]);
        }
        $this->addFlash('danger','Jeton invalide');
        return $this->redirectToRoute('app_login');
    }

}
