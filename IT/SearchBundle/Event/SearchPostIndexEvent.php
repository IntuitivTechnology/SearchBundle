<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 25/01/17
 * Time: 16:31
 */

namespace IT\SearchBundle\Event;


use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\Event;

/**
 * Class SearchPostIndexEvent
 *
 * Event fired after the complete index refresh
 *
 * @package IT\SearchBundle\Event
 */
class SearchPostIndexEvent extends Event
{

    /** @var EntityManager $entityManager */
    protected $entityManager;

    /** @var array $indexes */
    protected $indexes;

    public function __construct(EntityManager $entityManager, array $indexes)
    {
        $this->entityManager = $entityManager;
        $this->indexes = $indexes;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Returns an array of IT\SearchBundle\Entity\SearchIndex
     * @return array
     */
    public function getIndexes()
    {
        return $this->indexes;
    }

}