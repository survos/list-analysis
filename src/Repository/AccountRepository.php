<?php

namespace App\Repository;

use App\Entity\Account;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class AccountRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Account::class);
    }

    /** @todo: filter by timeframe, loc */
    public function findTopAccounts($count = 10)
    {
        return $this->createQueryBuilder('a')
            // ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.count', 'DESC')
            ->setMaxResults($count)
            ->getQuery()
            ->getResult()
            ;
    }

    public function topAccountsMessageCount($accountLimit = 10)
    {
        // better to do an actual count, but this works
        $count = 0;
        /** @var Account $account */
        foreach ($this->findTopAccounts($accountLimit) as $account) {
            $count += $account->getCount();
        }
        return $count;
    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('a')
            ->where('a.something = :value')->setParameter('value', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
