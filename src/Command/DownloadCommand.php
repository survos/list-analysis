<?php

namespace App\Command;

use App\Entity\Archive;
use Doctrine\ORM\EntityManagerInterface;
use Goutte\Client;
use GuzzleHttp\Cookie\CookieJar;
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
            ->addOption('refresh', null, InputOption::VALUE_NONE, 'Refresh Monthly List')
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
        $base = 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/';

// makes a real request to an external site
        $client = new Client();
        $crawler = $client->request('GET', $base);

// select the form and fill in some values
        $form = $crawler->selectButton('submit')->form();
        $form['username'] = 'tacman@gmail.com';
        $form['password'] = 'ozwoupid';


// submits the given form
        $crawler = $client->submit($form);



        /*

        $crawler = $client->request('GET', 'http://list.rappnet.org/mailman/private/rappnet_list.rappnet.org/2018-September.txt.gz');
        $io->writeln(sprintf("%s bytes", $client->getResponse()->getContent())); die();
        */

        // hack, regex the links
        $text = $client->getResponse();

        // this doesn't seem quite right
        $em = $this->em; // $this->getContainer()->get('doctrine');
        $repo = $em->getRepository(Archive::class);


        if ($input->getOption('refresh'))
        {
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
        }




        $cookieJar = $client->getCookieJar();
        $guzzleClient = $client->getClient();
        $jar = CookieJar::fromArray($cookieJar->all(), 'list.rappnet.org');
        dump($jar, $cookieJar);

        foreach ($repo->findBy([], ['id' => 'DESC']) as $archive) {
            $url = $base . $archive->getFilename();
            $savedFile = "../data/" . $archive->getFilename();

            $crawler = $client->request('GET', $url);
            file_put_contents($savedFile, $client->getResponse()->getContent());
            $io->writeln(sprintf("%s: %s bytes", $savedFile, filesize($savedFile)));



            die();


            $response = $guzzleClient->get($url, ['sink' => $savedFile]);
            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");

            $response = $client->request('GET', $url, [ 'sink' => $savedFile]);
            $client->getResponse()->getContent();

            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");


            $response = $guzzleClient->get($url, ['cookies' => $jar, 'sink' => $savedFile]);

            $io->writeln($savedFile . " downloaded: " . filesize($savedFile));
            die("Stopped");
        }




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

        $io->success('Finished download.');
    }
}
