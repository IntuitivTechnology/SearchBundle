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
use Knp\Component\Pager\PaginatorInterface;
use IT\SearchBundle\Entity\SearchIndex;

class DatabaseSearcher implements SearcherInterface
{

    /** @var EntityManager $em */
    protected $em;

    /** @var PaginatorInterface $paginator */
    protected $paginator;

    /** @var string $minScore */
    protected $minScore;

    /** @var bool $useResultCache */
    protected $useResultCache;

    /**
     * DatabaseSearcher constructor.
     *
     * @param EntityManager      $em
     * @param PaginatorInterface $paginator
     */
    public function __construct(EntityManager $em, PaginatorInterface $paginator, $minScore, $useResultCache)
    {
        $this->em = $em;
        $this->paginator = $paginator;
        $this->minScore = $minScore;
        $this->useResultCache = $useResultCache;
    }

    /**
     * Search entities in the indexed items using "MATCH AGAINST" MySQL function.<br>
     * First searches "IN NATURAL LANGUAGE MODE" into the database.<br>
     * If no result was found, try to search "IN NATURAL LANGUAGE MODE WITH QUERY EXPANSION".<br>
     *
     * @param string $terms            Terms to search
     * @param int    $page             For pagination, put the page you need, default to 1
     * @param int    $limit            For the pagination, put the limit of the request, default to 10
     * @param array  $entityClassnames An array of full classnames. If the array is empty, the services searches on all entities
     *
     * @return SlidingPagination
     */
    public function search($terms, $page = 1, $limit = 10, array $entityClassnames = array(), $enableLikeSearch  = false)
    {

        /** @var SlidingPagination $indexes */
        if ($enableLikeSearch) {
            $indexes = $this->paginator->paginate(
                $this->em->getRepository('ITSearchBundle:SearchIndex')->searchLikeQB($terms, $entityClassnames, $this->useResultCache),
                $page,
                $limit
            );
        }

        if (!isset($indexes) || !($indexes instanceof SlidingPagination) || $indexes->getTotalItemCount() <= 0) {
            $indexes = $this->paginator->paginate(
                $this->em->getRepository('ITSearchBundle:SearchIndex')->searchQB($terms, $entityClassnames, $this->minScore, $this->useResultCache),
                $page,
                $limit
            );
        }

        if ($indexes->getTotalItemCount() <= 0) {
            $indexes = $this->paginator->paginate(
                $this->em->getRepository('ITSearchBundle:SearchIndex')->searchExpandedQB($terms, $entityClassnames, $this->minScore, $this->useResultCache),
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
            if (is_array($index)) {
                $index = $index[0];
            }

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
            if (is_array($index)) {
                $index = $index[0];
            }

            if (array_key_exists($index->getClassname(), $objects) && array_key_exists($index->getIdentifier(), $objects[$index->getClassname()])) {
                $results[] = $objects[$index->getClassname()][$index->getIdentifier()];
            }

        }

        $indexes->setItems($results);

        return $indexes;
    }

}