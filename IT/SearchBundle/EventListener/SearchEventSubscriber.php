<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 30/01/17
 * Time: 16:04
 */

namespace IT\SearchBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use IT\SearchBundle\Entity\SearchIndex;
use IT\SearchBundle\Services\DatabaseIndexer;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SearchEventSubscriber implements EventSubscriber
{

    /** @var bool $enableEventListener */
    protected $enableEventListener = false;

    /** @var array $indexConfig */
    protected $indexConfig;

    /** @var DatabaseIndexer $indexer */
    protected $container;

    /** @var bool $useResultCache */
    protected $useResultCache;

    /**
     * SearchEventSubscriber constructor.
     *
     * @param                    $enableEventListener
     * @param array              $indexConfig
     * @param ContainerInterface $container
     */
    public function __construct($enableEventListener, array $indexConfig, ContainerInterface $container, $useResultCache)
    {
        $this->enableEventListener = $enableEventListener;
        $this->indexConfig = $indexConfig;
        $this->container = $container;
        $this->useResultCache = $useResultCache;

    }

    /**
     * @inheritdoc
     */
    public function getSubscribedEvents()
    {

        return array(
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
        );
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {

        if (!$this->enableEventListener) {
            return;
        }

        $this->updateIndexIfNeeded($args);
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        if (!$this->enableEventListener) {
            return;
        }

        $this->updateIndexIfNeeded($args);
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @throws \Exception
     */
    public function preRemove(LifecycleEventArgs $args)
    {


        if (!$this->enableEventListener) {
            return;
        }

        foreach ($this->indexConfig as $config) {
            if ($config['classname'] == get_class($args->getEntity())) {
                $this->getIndexer()->removeIndex($args->getEntity());

                if ($this->useResultCache === true) {
                    $cacheDriver = $args->getEntityManager()->getConfiguration()->getResultCacheImpl();
                    $cacheDriver->delete(SearchIndex::CACHE_INDEX);
                }

                return;
            }
        }
    }

    /**
     * If the entity is mapped for search, creates a new index or update existing
     *
     * @param $entity
     *
     * @throws \Exception
     */
    protected function updateIndexIfNeeded(LifecycleEventArgs $args)
    {

        $entity = $args->getEntity();

        foreach ($this->indexConfig as $config) {
            if ($config['classname'] == get_class($entity)) {
                $this->getIndexer()->updateIndex($entity);

                if ($this->useResultCache === true) {
                    $cacheDriver = $args->getEntityManager()->getConfiguration()->getResultCacheImpl();
                    $cacheDriver->delete(SearchIndex::CACHE_INDEX);
                }

                return;
            }
        }
    }

    /**
     * @return DatabaseIndexer
     */
    protected function getIndexer()
    {
        return $this->container->get('it_search.database.indexer');
    }

}