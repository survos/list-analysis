<?php
/**
 * Created by PhpStorm.
 * User: tac
 * Date: 1/26/19
 * Time: 6:49 AM
 */

namespace App\Services;


use App\Entity\Invitation;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class InvitationService
{

    private $userManager;
    private $entityManager;
    private $twig;
    private $router;
    private $mailer;

    public function __construct(UserManagerInterface $userManager, EntityManagerInterface $entityManager,
                                \Twig_Environment $twig, RouterInterface $router, \Swift_Mailer $mailer)
    {
        $this->userManager = $userManager;
        $this->twig = $twig;
        $this->router = $router;
        $this->entityManager = $entityManager;
        $this->mailer = $mailer;
    }

    /**
     * @param array $options
     * @return Invitation|null|object
     */
    public function inviteUser(array $options = []): Invitation {
        $repo = $this->entityManager->getRepository(Invitation::class);
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'email' => null,
            'pendingData' => [],
            'sendEmail' => true,
            'target' => 'subscriber',
        ]);
        $resolver->setAllowedValues('target', ['subscriber','admin']);
        $resolver->setAllowedTypes('email', ['string', 'null']);
        $resolver->setAllowedTypes('pendingData', ['array']);
        $resolver->setAllowedTypes('sendEmail', ['boolean']);
        $options = $resolver->resolve($options);
        $email = $options['email'];

        // canonical?  Or maybe use UserManager?

        if ($email && $this->userManager->findUserByEmail($email)) {
            throw new \LogicException(sprintf('Account with email "%s" already exists', $email));
        }
        // was if ($email && $this->em->getRepository(User::class)->findOneBy(['email' => $email])) {

        $invitation = $email ? $repo->findOneBy(['email' => $email]) : false;
        if (!$invitation) {
            $invitation = new Invitation();
            $invitation->setEmail($email ?: null);
            $invitation->setPendingData($options['pendingData']);

            $this->entityManager->persist($invitation);
        }
        if (false && $email) {
            $ic = $invitation->getCode();
            $template = 'email/subscriberInvitation.html.twig';
            if ($options['target'] === 'subscriber') {
                $link = $this->router->generate('fos_user_registration_register', ['ic' => $invitation->getCode()]);
            } else {
                // $link = $this->getObserveUrl(['project' => $project, 'ic' => $invitation->getCode()]);
                $template = 'email/participantWelcome.html.twig';
            }
            try {
                $body = $this->twig->render($template, ['link' => $link, 'ic' => $ic]);
            } catch (\Exception $e) {
                throw new \Exception($e->getMessage());
            }

            $message = (new \Swift_Message("Invitation to join"))
                ->setFrom(getenv('mailer_user'))
                ->setTo($invitation->getEmail())
                ->setBody($body, 'text/html'
                )
                /*
                 * If you also want to include a plaintext version of the message
                ->addPart(
                    $this->renderView(
                        'emails/registration.txt.twig',
                        ['name' => $name]
                    ),
                    'text/plain'
                )
                */
            ;

            $this->mailer->send($message);

            /*
            if ($this->container->getParameter('okay_to_connect')) {
                $sent = $this->container->get('posse.email')->sendPlainEmail(
                    $email,
                    sprintf('%s Invitation', $project->getCode()),
                    $body
                );
            } else {
                $sent = false;
            }
            */
        } else {
            $sent = true;
        }
        /*
        if (!$invitation->isSent()) {
            $invitation->setSent($sent);
        }
        */
        return $invitation;
    }

}