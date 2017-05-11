<?php

namespace IT\SearchBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * IT\SearchBundle\Entity\SearchIndex
 *
 * @ORM\Entity(repositoryClass="IT\SearchBundle\Entity\Repository\SearchIndexRepository")
 * @ORM\Table(name="search_index", options={"engine"="MyISAM"}, indexes={@ORM\Index(columns={"content"}, flags={"fulltext"})})
 */
class SearchIndex
{

    const CACHE_INDEX = 'search_index';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    /**
     * @ORM\Column(name="identifier", type="integer")
     */
    protected $identifier;

    /**
     * @ORM\Column(name="classname", type="string", length=255)
     */
    protected $classname;


    public function __construct()
    {
    }

    /**
     * Set the value of id.
     *
     * @param integer $id
     *
     * @return \IT\SearchBundle\Entity\SearchIndex
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of id.
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getClassname()
    {
        return $this->classname;
    }

    /**
     * @param mixed $classname
     */
    public function setClassname($classname)
    {
        $this->classname = $classname;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
        return $this;
    }

}
