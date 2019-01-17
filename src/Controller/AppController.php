<?php

namespace App\Controller;

use App\Repository\AccountRepository;
use App\Repository\MessageRepository;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\ORM\EntityManagerInterface;
use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/landing", name="landing")
     */
    public function index(LandingService $landingService)
    {
        return $this->render('test_menu_index.html.twig', [
            'entities' => $landingService->getEntities()
        ]);
    }

    /**
     * @Route("/pie", name="pie")
     */
    public function piechart(Request $request, AccountRepository $repo, MessageRepository $messageRepository)
    {
        $accountLimit = $request->get('limit', 10);
        $topAccounts = $repo->findTopAccounts($accountLimit);

        // g
        $pieChart = new PieChart();

        $total = $messageRepository->count([]);
        $topTotal = 0;

        $data = [['Account', 'MessageCount']]; // first row
        foreach ($topAccounts as $account) {
            array_push($data, [
                sprintf("%s - %s", $account->getSenderName(), number_format($account->getCount(), 0)), $account->getCount()]);
            $topTotal += $account->getCount();
        }
        array_push($data, ['Everyone Else', $total - $topTotal]);

        $pieChart->getData()->setArrayToDataTable($data);

        $pieChart->getOptions()->setTitle(sprintf('Top %d RappNet Posters', $accountLimit)) ;
        $pieChart->getOptions()->setHeight(900);
        $pieChart->getOptions()->setWidth(1500);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        return $this->render('app/index.html.twig', array('piechart' => $pieChart));
    }


}
