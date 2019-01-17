<?php

namespace App\Command;

use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdateCountsCommand extends Command
{
    protected static $defaultName = 'app:update-counts';

    private $em;

    public function __construct(EntityManagerInterface $em, ?string $name = null)
    {
        $this->em = $em;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Update Account Message Counts')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $accountRepo = $this->em->getRepository(Account::class);
        foreach ($accountRepo->findAll() as $account) {
            $account->setCount($account->getMessages()->count());
        }

        $this->em->flush();

        $io->success('Account Message Counts updated.');
    }
}
