<?php

namespace App\Command;

use App\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DownloadCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:download';

    private $em;

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    public function __construct($name = null, EntityManagerInterface $entityManager)
    {
        parent::__construct($name);
        $this->em = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

// makes a real request to an external site
        $client = new Client();
        $crawler = $client->request('GET', 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/');

// select the form and fill in some values
        $form = $crawler->selectButton('submit')->form();
        $form['username'] = 'tacman@gmail.com';
        $form['password'] = 'ozwoupid';

// submits the given form
        $crawler = $client->submit($form);


        // $crawler = $client->request('GET', 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/');

        // hack, regex the links
        $text = $client->getResponse();

        // this doesn't seem quite right
        $em = $this->em; // $this->getContainer()->get('doctrine');
        $repo = $em->getRepository(Archive::class);
        if (preg_match_all('/href="(.*?\.gz)"/', $text, $mm))
        {
            foreach ($mm[1] as $filename) {
                if (!$archive = $repo->findOneBy(['filename' => $filename])) {
                    $archive = (new Archive())
                        ->setFilename($filename);
                    $em->persist($archive);
                }
            }
        } else {
            $io->error("No gz files found");
        }
        $em->flush();

        /* this is better..
        foreach ($crawler->links() as $link) {
            dump($link); die("Link");
        }
        $crawler->filter('a')->each(function ($node) {
            print $node->text()."\n";
            dump($node); die("Stopped after node dump");
        });
        */

        // dump the results
        // dump($client->getResponse());
        die("Stopped.");

        $client->request('GET', 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/2018-September.txt.gz');

        $arg1 = $input->getArgument('arg1');

        if ($arg1) {
            $io->note(sprintf('You passed an argument: %s', $arg1));
        }

        if ($input->getOption('option1')) {
            // ...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');
    }
}
