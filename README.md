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
$results = $databaseSearcher->search($terms);
```


## How to index content

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