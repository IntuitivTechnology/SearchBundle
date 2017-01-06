<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 02/01/17
 * Time: 10:11
 */

namespace IT\SearchBundle\Services;


use Doctrine\ORM\EntityManager;
use IT\SearchBundle\Entity\SearchIndex;

class DatabaseIndexer
{

    /** @var EntityManager $em */
    protected $em;

    /** @var array $indexConfig */
    protected $indexConfig;

    public function __construct(EntityManager $em, array $indexConfig)
    {
        $this->em = $em;
        $this->indexConfig = $indexConfig;
    }

    /**
     * Purge the index table and build all the index
     *
     * @return array
     * @throws \Exception
     */
    public function indexContent()
    {

        // Purge all index before reindexing
        $this->purgeIndex();

        $searchIndexes = array();

        /** @var array $index */
        foreach ($this->indexConfig as $index) {
            $classname = $index['classname'];
            $fields = $index['fields'];
            $filters = $index['filters'];
            $identifierField = $index['identifier'];

            $repository = $this->em->getRepository($classname);

            if ($repository) {

                // Fetch the objects to index
                $objects = $repository->findBy($filters);

                foreach ($objects as $object) {
                    $content = '';

                    // Build index content
                    foreach ($fields as $fieldname) {
                        $getter = 'get' . ucfirst($fieldname);

                        if (method_exists($object, $getter)) {
                            if ($content != '') {
                                $content .= ' ';
                            }

                            $content .= $object->$getter();
                        } else {
                            throw new \Exception(sprintf('The entity %s must provide a method names %s to be properly indexed', $classname, $getter));
                        }
                    }

                    // Get the identifier value
                    $identifierGetter = 'get' . ucfirst($identifierField);
                    $identifier = null;
                    if (method_exists($object, $identifierGetter)) {
                        $identifier = $object->$identifierGetter();
                    } else {
                        throw new \Exception(sprintf('The entity %s must provide a method names %s to be properly indexed', $classname, $identifierGetter));
                    }


                    $searchIndex = new SearchIndex();
                    $searchIndex
                        ->setContent(strip_tags(strtolower($content)))
                        ->setClassname($classname)
                        ->setIdentifier($identifier)
                    ;
                    $this->em->persist($searchIndex);

                    $searchIndexes[] = $searchIndex;
                }
            }
        }

        $this->em->flush();

        return $searchIndexes;
    }

    /**
     * Update one object index
     *
     * @param $object
     * @throws \Exception
     */
    public function updateIndex($object)
    {
        /** @var array $index */
        foreach ($this->indexConfig as $index) {
            $classname = $index['classname'];

            if ($classname != get_class($object)) {
                continue;
            }

            $fields = $index['fields'];
            $identifierField = $index['identifier'];

            $repository = $this->em->getRepository($classname);

            if ($repository) {

                $content = '';


                // Build index content
                foreach ($fields as $fieldname) {
                    $getter = 'get' . ucfirst($fieldname);

                    if (method_exists($object, $getter)) {
                        if ($content != '') {
                            $content .= ' ';
                        }

                        $content .= $object->$getter();
                    } else {
                        throw new \Exception(sprintf('The entity %s must provide a method names %s to be properly indexed', $classname, $getter));
                    }
                }

                // Get the identifier value
                $identifierGetter = 'get' . ucfirst($identifierField);
                $identifier = null;
                if (method_exists($object, $identifierGetter)) {
                    $identifier = $object->$identifierGetter();
                } else {
                    throw new \Exception(sprintf('The entity %s must provide a method names %s to be properly indexed', $classname, $identifierGetter));
                }

                $searchIndex = $this->em->getRepository('ITSearchBundle:SearchIndex')->findOneBy(array(
                    'identifier' => $identifier,
                    'classname' => $classname,
                ));

                if (!($searchIndex instanceof SearchIndex)) {
                    $searchIndex = new SearchIndex();

                    $searchIndex
                        ->setClassname($classname)
                        ->setIdentifier($identifier)
                    ;
                }

                $searchIndex
                    ->setContent(strip_tags(strtolower($content)))
                ;

                $this->em->persist($searchIndex);

            }
        }

        $this->em->flush();
    }

    /**
     * Purge the index table
     *
     * @throws \Doctrine\DBAL\ConnectionException
     */
    protected function purgeIndex()
    {
        $cmd = $this->em->getClassMetadata('ITSearchBundle:SearchIndex');
        $tableName = $cmd->getTableName();

        $connection = $this->em->getConnection();

        $connection->beginTransaction();

        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0');
            $connection->query('DELETE FROM '.$tableName);
            // Beware of ALTER TABLE here--it's another DDL statement and will cause
            // an implicit commit.
            $connection->query('SET FOREIGN_KEY_CHECKS=1');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
            throw new $e;
        }
    }


}