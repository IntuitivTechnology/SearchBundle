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
    - { resource: ../../src/SearchBundle/Resources/config/config.yml }

it_search:
  indexes:
    projects:
      classname: 'CoreBundle\Entity\EntityFQCN'
      identifier: id #identifierFieldName
      fields:
        - title
        - description
        - cityArea
        - theme
        #Other fields
      filters:
        isPublished: true #array used in findBy method
    news:
      classname: 'CoreBundle\Entity\EntityFQCN2'
      identifier: id #identifierFieldName
      fields:
        - title
        - catchphrase
        - content
        #Other fields
      filters:
        published: true #array used in findBy method

```
## CLI Tools

To manually index the fields, use the following command :
```bash
php app/console search:index:build -d
```

## How to use the bundle

In your controller, use the following lines to get the results in your search page :

The **search()** function returns a paginator object.
```
$databaseSearcher = $this->get('it_search.database.searcher');
$results = $databaseSearcher->search($terms);
```
