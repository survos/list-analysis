<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\TimePeriod;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MessageController extends Controller
{
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
    public function message()
    {
        $repo = $this->getDoctrine()->getRepository(Message::class);
        $messages = $repo->findBy([], null, 4);
        return $this->render("message/list.html.twig", [
            'messages' => $messages
        ]);
    }
}
