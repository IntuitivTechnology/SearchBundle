<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 02/01/17
 * Time: 10:07
 */

namespace IT\SearchBundle\Services;


interface SearcherInterface
{

    /**
     * Method user to search into site content
     *
     * @param $terms
     *
     * @return mixed
     */
    public function search($terms);

}