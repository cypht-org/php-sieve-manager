# php-sieve-manager

A native PHP library for managing the ManageSieve protocol (RFC5228) and generate basic Sieve scripts. Used by [Cypht Webmail](https://cypht.org) and available to all PHP projects via [https://packagist.org/packages/henrique-borba/php-sieve-manager](https://packagist.org/packages/henrique-borba/php-sieve-manager)

[Tiki Wiki CMS Groupware bundles Cypht webmail](https://doc.tiki.org/Cypht)  and [extends filters beyond what is possible via the Sieve protocol](https://doc.tiki.org/Email-filters).

# How to use

### Connect to ManageSieve
```php
require_once "vendor/autoload.php";

$client = new \PhpSieveManager\ManageSieve\Client("localhost", 4190);
$client->connect("test@localhost", "mypass", false, "", "PLAIN");


$client->listScripts();
```


### Generate Sieve script
```php
$filter = \PhpSieveManager\Filters\FilterFactory::create('MaxFileSize');


$criteria = \PhpSieveManager\Filters\FilterCriteria::if('body')->contains('"test"');

// Messages bigger than 2MB will be rejected with an error message
$size_condition = new \PhpSieveManager\Filters\Condition(
    "Messages bigger than 2MB will be rejected with an error message", $criteria
);

$size_condition->addCriteria($criteria);
$size_condition->addAction(
     new \PhpSieveManager\Filters\Actions\DiscardFilterAction()
);


// Add the condition to the Filter
$filter->setCondition($size_condition);
$filter->toScript();
```
