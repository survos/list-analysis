<?php

namespace App\Controller;

use App\Entity\Account;
use App\Entity\Invitation;
use App\Entity\Message;
use App\Entity\TimePeriod;
use App\Form\InvitationFormType;
use App\Form\MessageSearchFormType;
use App\Repository\AccountRepository;
use App\Repository\MessageRepository;
use App\Repository\TimePeriodRepository;
use App\Services\InvitationService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\ORM\EntityManagerInterface;
use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Voryx\ThruwayBundle\Annotation\Register;

class AppController extends AbstractController
{

    private $messageRepo;
    private $accountRepo;
    private $timePeriodRepo;

    public function __construct(MessageRepository $messageRepository, AccountRepository $accountRepository, TimePeriodRepository $timePeriodRepository)
    {
        $this->messageRepo = $messageRepository;
        $this->accountRepo = $accountRepository;
        $this->timePeriodRepo = $timePeriodRepository;
    }

    /**
     * @Register("com.example.add")
     */
    public function addAction($num1, $num2)
    {
        return $num1 + $num2;
    }

    /**
     * @Register("com.example.multiply")
     */
    public function multiply($num1, $num2)
    {
        return $num1 * $num2;
    }

    /**
     * @  Route("/landing", name="landing")
     */
    public function index(LandingService $landingService)
    {
        return $this->render('test_menu_index.html.twig', [
            'entities' => $landingService->getEntities()
        ]);
    }

    /**
     * @Route("/archives", name="archives")
     */
    public function archives(LandingService $landingService)
    {
        return $this->render('archives.html.twig', [
        ]);
    }

    private function getChart()
    {
        // $chart = new \CMEN\GoogleChartsBundle\GoogleCharts\Charts\SteppedAreaChart();
        $chart = new \CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart();
        $chart->getData()->setArrayToDataTable([
            ['Year', 'Sales', 'Expenses', 'Profit'],
            ['2014', 1000, 400, 200],
            ['2015', 1170, 460, 250],
            ['2016', 660, 1120, 300],
            ['2017', 1030, 540, 350]
        ]);

        $chart->getOptions()->getChart()
            ->setTitle('Company Performance')
            ->setSubtitle('Sales, Expenses, and Profit: 2014-2017');
        $chart->getOptions()
            ->setBars('vertical')
            ->setHeight(400)
            ->setWidth(900)
            ->setColors(['#1b9e77', '#d95f02', '#7570b3'])
            ->getVAxis()
            ->setFormat('decimal');

        return $chart;
    }

    private function getMonthlyChartData()
    {

        $y = $m = [];
        /** @var TimePeriod $period */
        foreach ($this->timePeriodRepo->findAll() as $period) {
            if (empty($yearArray[$period->getYear()])) {
                $yearArray[$period->getYear()] = 0;
            }
            array_push($m, [$period->__toString(), $period->getImportedMessageCount()]);
            $yearArray[$period->getYear()] += $period->getImportedMessageCount();
        }

        foreach ($yearArray as $year => $yearData) {
            array_push($x, [$year, $yearData]);
        }

        return $m;
    }

    /**
     * @Route("/monthly-counts.{_format}", name="monthly_count_data")
     */
    public function monthlyCountData(Request $request, $_format='json', AccountRepository $repo, MessageRepository $messageRepository, TimePeriodRepository $timePeriodRepository)
    {
        return new JsonResponse($this->getMonthlyChartData());
    }



    private function getMonthlyChart(TimePeriodRepository $timePeriodRepository)
    {
        $chart = new \CMEN\GoogleChartsBundle\GoogleCharts\Charts\Material\ColumnChart();
        $yearArray = [];

        $x = [['Period', '# of Messages']];
        /** @var TimePeriod $period */
        foreach ($timePeriodRepository->findAll() as $period) {
            if (empty($yearArray[$period->getYear()])) {
                $yearArray[$period->getYear()] = 0;
            }
            array_push($x, [$period->__toString(), $period->getImportedMessageCount()]);
            $yearArray[$period->getYear()] += $period->getImportedMessageCount();
        }

        foreach ($yearArray as $year => $yearData) {
            // array_push($x, [$year, $yearData]);
        }

        // dump($x); die();
        $chart->getData()->setArrayToDataTable($x);

        $chart->getOptions()->getChart()
            ->setTitle('Rappnet Messages, By Month')
            ->setSubtitle('2006-2019');
        $chart->getOptions()
            ->setBars('vertical')
            ->setHeight(300)
            // ->setWidth(800)
            ->setColors(['#1b9e77', '#d95f02', '#7570b3']);
        $chart
            ->getOptions()->getHAxis()
                ->setFormat('');
        $chart->getOptions()
            ->getVAxis()
               ->setFormat('decimal')
        ;

        return $chart;
    }


    /**
     * @Route("/chart", name="chart")
     */
    public function chart(Request $request, AccountRepository $repo, MessageRepository $messageRepository, TimePeriodRepository $timePeriodRepository)
    {
        return $this->render('app/chart.html.twig', [

        ]);


    }

    private function getMessageCountData($options)
    {
        $options = (new OptionsResolver())
            ->setDefaults([
                'startDate' => null,
                'endDate' => null,
                'accountLimit' => 10
            ])->resolve($options);

        $qb = $this->messageRepo->createQueryBuilder('message')
            ->join('message.account', 'account');


        if ($startDate = $options['startDate']) {
            $qb->andWhere('message.time >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate = $options['endDate']) {
            $qb->andWhere('message.time <= :endDate')
                ->setParameter('endDate', $endDate);
        }

        // first, get the total number of messages
        $total = $qb->select('count(message.id)')->getQuery()->getSingleScalarResult();


        $qb
            ->select('account.id, count(message.id) as cnt')
            // ->from(Message::class, 'c')
            ->groupBy('account.id')
            ->orderBy('cnt', 'DESC')
        ;

        $topAccounts = $qb->setMaxResults($options['accountLimit'])->getQuery()->getResult();

        $data = [];
                // $data = [['Account', 'MessageCount']]; // first row
        $topTotal = 0;


        /** @var Account $account */
        foreach ($topAccounts as $accountData) {
            $account = $this->accountRepo->find($accountData['id']);
            array_push($data, [
                sprintf("%s - %s", $account->getShortName(), number_format($c = $accountData['cnt'], 0)), $c]);
            $topTotal += $c;
        }
        $everyoneElse = $total - $topTotal;
        // could also sort the array
        array_unshift($data, [sprintf('Everyone Besides Top  (%s)', number_format($everyoneElse)), $everyoneElse]);


        return [
            'topAccounts' => $data,
            'total' => $total,
            'topTotal' => $topTotal,
            // 'data' => $data
        ];
    }

    /**
     * @Route("/message-counts.{_format}", name="message_count_data")
     */
    public function messageCountData(Request $request, $_format='json', AccountRepository $repo, MessageRepository $messageRepository, TimePeriodRepository $timePeriodRepository)
    {
        return new JsonResponse($this->getMessageCountData(
            [
                'startDate' => $request->get('startDate'),
                'endDate' => $request->get('endDate'),
                'accountLimit' => $request->get('accountLimit', 8)
            ]
        ));
    }

    /**
     * @Route("/x", name="request_invitation")
     */
    public function invitation(Request $request, EntityManagerInterface $em, InvitationService $invitationService, \Swift_Mailer $mailer, RouterInterface $router)
    {
        $defaults = [
            'email' => sprintf('tacman+%s@gmail.com', time())
        ];

        $invitation = null;

        $builder = $this->createFormBuilder($defaults);

        $builder->add('email', EmailType::class, [

        ])
            ->add('submit', SubmitType::class, [
                'label' => "Request Invitation"
            ]);

        $form = $builder->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $form->get('email')->getData();
            // check if it's in the database already
            if (!$invitation = $em->getRepository(Invitation::class)->findOneBy(['email' => $email])) {
                $invitation = $invitationService->inviteUser(['email' => $email]);
                // $em->persist($invitation);
                $em->flush();

                if ($invitation && $email) {
                    $ic = $invitation->getCode();
                    $template = 'email/subscriberInvitation.html.twig';
                    $link = $router->generate('fos_user_registration_register', ['ic' => $invitation->getCode()], UrlGeneratorInterface::ABSOLUTE_URL );

                    try {
                        $body = $this->renderView($template, [
                            'email' => $email,
                            'link' => $link,
                            'ic' => $ic]);
                    } catch (\Exception $e) {
                        throw new \Exception($e->getMessage());
                    }

                    try {
                        $message = (new \Swift_Message("Invitation to join"))
                            ->setFrom(getenv('mailer_user'))
                            ->setTo($email)
                            ->setBody($body, 'text/html'
                            )/*
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

                        $sent = $mailer->send($message, $failed);
                        if (empty($failed)) {
                            $this->addFlash('success', "An invitation has been sent to " . $invitation->getEmail());
                        } else {
                            $this->addFlash('error', "Send Error: " . $invitation->getEmail());
                        }

                    } catch (\Exception $e) {
                        $this->addFlash('error', "Error sending to " . $invitation->getEmail());
                        $this->addFlash('error', $e->getMessage());

                    }


                    return $this->redirectToRoute('request_invitation');
                }
            }
        }

        // $form = $this->createForm(InvitationFormType::class); // , $invitation);

        return $this->render('app/landing.html.twig', [
            'form' => $form->createView(),
            'invitation' => $invitation
        ]);

    }


    /**
     * @Route("/landing", name="landing")
     * @Route("/pie", name="pie")
     */
    public function piechart(Request $request, AccountRepository $repo, MessageRepository $messageRepository, TimePeriodRepository $timePeriodRepository)
    {


        /** @var Message $newestMessage */
        $newestMessage = $messageRepository->findOneBy([], ['time' => 'DESC']);
        // $oldestMessage = new \DateTime('2006-01-01'); // $messageRepository->findOneBy([], ['time' => 'ASC']);


        $defaults = [
            'accountLimit' => 10,
            // 'startDate' => '10/16/2013',
            'startDate' => new \DateTime('2006-01-01'), // $oldestMessage->getTime(),
            'endDate' => $newestMessage ? $newestMessage->getTime() : null
        ];

        $searchForm = $this->createForm(MessageSearchFormType::class, $defaults);
        $searchForm->handleRequest($request);


        $accountLimit = $searchForm->get('accountLimit')->getData();



        // $topAccounts = $repo->findTopAccounts($accountLimit);


        // g
        $pieChart = new PieChart();

        $dataSummary = $this->getMessageCountData($defaults);

        /*

        $pieChart->getData()->setArrayToDataTable($dataSummary['topAccounts']);

        $pieChart->getOptions()->setTitle(sprintf('Top %d RappNet Posters', $accountLimit)) ;
        $pieChart->getOptions()->setHeight(400);
        $pieChart->getOptions()->setWidth(800);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);\
        */

        return $this->render('app/index.html.twig',
            array_merge($dataSummary,

            [
            'searchForm' => $searchForm->createView(),
            // 'piechart' => $pieChart,
            'columnChart' => $this->getMonthlyChart($timePeriodRepository),
            'timePeriods' => $timePeriodRepository->findAll()
        ]) );
    }


}
