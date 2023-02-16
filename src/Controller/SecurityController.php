<?php

namespace App\Controller;

use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\UsersRepository;
use App\Service\SendMailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route('oublie-mdp', name: 'forgotten_password')]
    public function forgottenPassword(
        Request $request,
        UsersRepository $usersRepository,
        TokenGeneratorInterface $tokenGeneratorInterface,
        EntityManagerInterface $em,
        SendMailService $mail
         ): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            // on va chercher l'utilisateur par son email
            $user = $usersRepository->findOneByEmail($form->get('email')->getData());

            // on verifie sii on a un utilisateur
            if($user){
                // on génère un token de réinitialisation
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $em->persist($user);
                $em->flush();

                // on génère un lien de réinitialisation du mot de pass
                $url = $this->generateUrl('reset_pass', ['token'=> $token], UrlGeneratorInterface::ABSOLUTE_URL);
                
                // on crée les données du mail
                $context = compact('url', 'user');

                // envoi du mail
            $mail->send(
                'no-reply@commercer.fr',
                $user->getEmail(),
                'Réinitialisation du mot de passe',
                'password_reset',
                $context
            );

            $this->addFlash('success', 'Email envoyé avec succès');
            return $this->redirectToRoute('app_login');


            }
            $this->addFlash('danger', 'un problème est survenue');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm'=> $form->createView()
        ]);
    }

    #[Route('/oublie-pass/{token}', name:'reset_pass')]
    public function resetPass(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UsersRepository $usersRepository
        ): Response
    {
        // on vérifie si on a ce token dans la base de donnée
        $user = $usersRepository->findOneByResetToken($token);


        if($user){
            $form = $this->createForm(ResetPasswordFormType::class);

            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid()){
                // on efface le token
                $user->setResetToken('');
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                    );
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success','Mot de passe changé avec succès');

                    return $this->redirectToRoute('app_login');


                }
            return $this->render('security/reset_password.html.twig',[
                'passForm'=> $form->createView()
            ]);

        };
        $this->addFlash('danger', 'jeton invalide');
        return $this->redirectToRoute('app_login');

    }
}
