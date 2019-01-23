<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\TimePeriod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\RegistryInterface;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findSubjectsSummary(TimePeriod $timePeriod = null)
    {
        $qb = $this->createQueryBuilder('message');
        $qb
            ->select('message.subject, COUNT(message.id) as cnt')
            // ->from(Message::class, 'c')
            ->groupBy('message.subject');
        if ($timePeriod) {
            $qb->andWhere('message.timePeriod = :timePeriod')
                ->setParameter('timePeriod', $timePeriod);
        }
        return $qb
            ->getQuery()->getResult();

    }

    public function getCountsByAccountQueryBuilder($startDate=null, $endDate=null, $maxResults=10): QueryBuilder
    {
        dump($startDate);
        $qb = $this->createQueryBuilder('message')
            ->join('message.account', 'account');

        $qb
            ->select('account.id, count(message.id) as cnt')
            // ->from(Message::class, 'c')
            ->groupBy('account.id')
            ->orderBy('cnt', 'DESC')
        ;

        if ($startDate) {
            $qb->andWhere('message.time >= :startDate')
                ->setParameter('startDate', $startDate);
        }

        if ($endDate) {
            $qb->andWhere('message.time <= :endDate')
                ->setParameter('endDate', $endDate);

        }


        return $qb;


    }

    /*
    public function findBySomething($value)
    {
        return $this->createQueryBuilder('m')
            ->where('m.something = :value')->setParameter('value', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */
}
