<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\TimePeriod;
use Solarium\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MessageController extends Controller implements ContainerAwareInterface
{

    use ContainerAwareTrait;

    /**
     * @Route("/message-index", name="index")
     */
    public function index()
    {
        $repo = $this->getDoctrine()->getRepository(TimePeriod::class);
        $periods = $repo->findBy([], null, 4);
        return $this->render("timePeriod/index.html.twig", [
            'periods' => $periods
        ]);
    }

    /**
     * @Route("/period/{id}")
     */
    public function period(TimePeriod $timePeriod)
    {
        return $this->render("timePeriod/show.html.twig", [
            'period' => $timePeriod,
            'subjects' => $this->getDoctrine()->getRepository(Message::class)->findSubjectsSummary($timePeriod)
        ]);
    }

    /**
     * @Route("/messages", name="message")
     */
    public function message(Request $request) // , Client $client)
    {
        $client = $this->get('solr.client');
        $searchString = $request->get('q', 'sperryville');

        $q = $client->createQuery(Message::class);
        $q->addSearchTerm('subject', $searchString);
        $messages = $q->getResult();
        /*
        $repo = $this->getDoctrine()->getRepository(Message::class);
        $messages = $repo->findBy([], null, 4);
        */
        return $this->render("message/list.html.twig", [
            'messages' => $messages,
            'searchString' => $searchString
        ]);
    }
}
