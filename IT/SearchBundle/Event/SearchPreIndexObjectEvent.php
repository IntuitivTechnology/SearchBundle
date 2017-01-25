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

class SearchPreIndexObjectEvent extends Event
{

    /** @var $object */
    protected $object;

    /** @var EntityManager $entityManager */
    protected $entityManager;

    public function __construct($object, EntityManager $entityManager)
    {
        $this->object = $object;
        $this->entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return mixed
     */
    public function getObject()
    {
        return $this->object;
    }

    /**
     * @param mixed $object
     */
    public function setObject($object)
    {
        $this->object = $object;
        return $this;
    }

}