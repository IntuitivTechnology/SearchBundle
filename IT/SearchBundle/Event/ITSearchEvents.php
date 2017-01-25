<?php
/**
 * Created by PhpStorm.
 * User: pvassoilles
 * Date: 25/01/17
 * Time: 16:27
 */

namespace IT\SearchBundle\Event;


class ITSearchEvents
{

    /**
     * Event name to identify pre-index of all objects
     */
    const PRE_INDEX = 'it_search.event.pre_index';

    /**
     * Event name to identify pre-index of one specific object
     */
    const PRE_INDEX_OBJECT = 'it_search.event.pre_index_object';

    /**
     * Event name to identify post-index of all objects
     */
    const POST_INDEX = 'it_search.event.post_index';

}