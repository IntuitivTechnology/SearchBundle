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
 * Class SearchPreIndexEvent
 *
 * Event fired before the complete index refresh
 *
 * @package IT\SearchBundle\Event
 */
class SearchPreIndexEvent extends Event
{

    /** @var EntityManager $entityManager */
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

}