# SearchBundle

Symfony2 Bundle : fulltext index and search for multiple Doctrine entities

## Installation

Install with composer :
```bash
composer require it/search-bundle
```

### Enable the bundle in your project

```php
// app/AppKernel.php
public function registerBundles()
{
    $bundles = array(
        // ...
        new IT\SearchBundle\ITSearchBundle(),
        // ...
    );
}
```

## Config

Add the following line to your `config.yml` :
```yaml
# app/config/config.yml

imports:
    - { resource: ../../vendor/it/search-bundle/IT/SearchBundle/Resources/config/config.yml }

it_search:
  indexes:
    enable_event_listener: ~ # Default to false, enables an Event listener that automatically update/create a mapped entity index
    min_score: ~ # Default to 0.8. Minimum score for the MATCH_AGAINST mysql function
    use_result_cache: ~ # Default to false. Enables the Doctrine result cache for 3600s
    projects:
      classname: 'ACMEBundle\Entity\EntityFQCN'
      identifier: id #identifier fieldName
      fields:
        - title
        - description
        #Other fields
      filters: #array used in a findBy method while indexing content
        isPublished: true
    news:
      classname: 'ACMEBundle\Entity\EntityFQCN2'
      identifier: id #identifier fieldName
      fields:
        - title
        - catchphrase
        - content
        #Other fields
      filters: #array used in a findBy method while indexing content
        published: true

```
## CLI Tools

To manually index the fields, use the following command :
```bash
php app/console search:index:build -d
```

## How to use the bundle

In your controller, use the following lines to get the results in your search page :

The **search()** function returns a SlidingPagination object (from Knp/Paginator).
```
$databaseSearcher = $this->get('it_search.database.searcher');
$results = $databaseSearcher->search($terms, $page = 1, $limit = 10, array $entityClassnames = array(), $enableLikeSearch  = false);
```
 _Note : When you call the "search" method, il you don't provide a list of classnames as 4th parameter, the service will search in all entities indexed._

## How to index content

### Manual indexing

The bundle provides one service with two methods to index content.

The following method clears the index table and reindex all contents :
```php
$this->get('it_search.database.indexer')->indexContent();
```

The second method update an object's index into the database :
```php
$this->get('it_search.database.indexer')->updateIndex($object);
```

There is no method for removing a specific index from the database for now. The feature will be implemented soon.

### Automatic indexing

New feature since 1.3.0 is an EventListener that automatically create, update and remove indexes when you create, update or remove entities that at mapped by the indexer.
As the listener is disabled by default, use this config to enable it :
```yaml
# app/config/config.yml

it_search:
  enable_event_listener: true
  # ...
```

_Note : The listener is disabled by default to stay compatible with older versions of the bundle_

## Events

Three event are dispatched during objects indexation :
- ITSearchEvents::PRE_INDEX
Dispatched at the beginning of the indexing.

- ITSearchEvents::PRE_INDEX_OBJECT
Dispatched before indexing a specific object. The object is available in the event object.

- ITSearchEvents::POST_INDEX
Dispatched after indexing all objects. The SearchIndex objects list is available in the event object.


### Example of use

Here is an example of an All-in-one event subscriber :

```php
// ACMEBundle\EventSubscriber\SearchEventSubscriber.php

use IT\SearchBundle\Event\ITSearchEvents;
use IT\SearchBundle\Event\SearchPostIndexEvent;
use IT\SearchBundle\Event\SearchPreIndexEvent;
use IT\SearchBundle\Event\SearchPreIndexObjectEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchEventSubscriber implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {

        return array(
            ITSearchEvents::PRE_INDEX           => 'preIndex',
            ITSearchEvents::PRE_INDEX_OBJECT    => 'preIndexObject',
            ITSearchEvents::POST_INDEX          => 'postIndex',
        );
    }

    public function preIndex(SearchPreIndexEvent $evt)
    {
        // Do stuff here
    }

    public function preIndexObject(SearchPreIndexObjectEvent $evt)
    {
        // Do stuff here
    }

    public function postIndex(SearchPostIndexEvent $evt)
    {
        // Do stuff here
    }

}
```

```xml
    <!--services.yml-->
    <services>
        <!--...-->
        <service id="acme.search.subscriber" class="ACMEBundle\EventSubscriber\SearchEventSubscriber">
            <tag name="kernel.event_subscriber"></tag>
        </service>
        <!--...-->
    </services>
```
OR, in Yaml :
```yml
#services.yml

services:
  acme.search.subscriber:
    class: "ACMEBundle\EventSubscriber\SearchEventSubscriber"
    tags:
      - { name: "kernel.event_subscriber" }
```
