<?php

namespace App\Repository;

use App\Entity\Message;
use Elasticsearch\Endpoints\MTermVectors;
use Elasticsearch\Endpoints\TermVectors;
use FOS\ElasticaBundle\Repository;
use Elastica\Query\BoolQuery;
use Elastica\Query\Terms;
use Elastica\Query;
use Pagerfanta\Pagerfanta;

class MessageSearchRepository extends Repository
{

    public function searchHeadlineText(Message $search): Pagerfanta
    {
        $query = new BoolQuery();

        if (!empty($search->getSubject())) {
            $query->addShould(
                [
                    new Terms('subject', [$search->getHeadlineText()])
                ]
            );
        }

        // return $this->findPaginated($query);


        $q = Query::create($query);
        $q
            ->setQuery($query)
            ->setSort(['time' => 'DESC'])
        ;

        // dump($query); die();


        return $this->findPaginated($q);
    }

    public function significantTerms()
    {
        $query = new TermVectors();
    }
}
