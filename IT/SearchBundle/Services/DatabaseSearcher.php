<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 02/01/17
 * Time: 10:07
 */

namespace IT\SearchBundle\Services;


use Doctrine\ORM\EntityManager;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
use Knp\Component\Pager\Paginator;
use Knp\Component\Pager\PaginatorInterface;
use IT\SearchBundle\Entity\SearchIndex;

class DatabaseSearcher implements SearcherInterface
{

    /** @var EntityManager $em */
    protected $em;

    /** @var PaginatorInterface $paginator */
    protected $paginator;

    public function __construct(EntityManager $em, PaginatorInterface $paginator)
    {
        $this->em = $em;
        $this->paginator = $paginator;
    }

    public function search($terms, $page = 1, $limit = 10)
    {
        /** @var SlidingPagination $indexes */
        $indexes = $this->paginator->paginate(
            $this->em->getRepository('ITSearchBundle:SearchIndex')->searchQB($terms),
            $page,
            $limit
        );

        if ($indexes->getTotalItemCount() <= 0) {
            $indexes = $this->paginator->paginate(
                $this->em->getRepository('ITSearchBundle:SearchIndex')->searchExpandedQB($terms),
                $page,
                $limit
            );
        }

        $results = array();

        $identifiers = array();
        $objects = array();


        // Separate the different entities indexes
        /** @var SearchIndex $index */
        foreach ($indexes as $index) {

            // $index[0] is the object SearchIndex
            $index = $index[0];

            if (!array_key_exists($index->getClassname(), $identifiers)) {
                $identifiers[$index->getClassname()] = array(
                    $index->getIdentifier(),
                );
            } else {
                $identifiers[$index->getClassname()][] = $index->getIdentifier();
            }
        }

        // Fetch the differents entities with their identifiers
        foreach ($identifiers as $classname => $identifier) {
            $databaseObjects = $this->em->getRepository($classname)->findBy(array(
                'id' => $identifier,
            ));

            if (count($databaseObjects) > 0) {
                $objects[$classname] = array();
            }

            foreach ($databaseObjects as $databaseObject) {
                $objects[$classname][$databaseObject->getId()] = $databaseObject;
            }

        }

        // Build a result array
        foreach ($indexes as $index) {
            $index = $index[0];

            if (array_key_exists($index->getClassname(), $objects) && array_key_exists($index->getIdentifier(), $objects[$index->getClassname()])) {
                $results[] = $objects[$index->getClassname()][$index->getIdentifier()];
            }

        }

        $indexes->setItems($results);

        return $indexes;
    }

}