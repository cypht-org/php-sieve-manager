# php-sieve-manager

A modern (started in 2022) PHP library for the ManageSieve protocol (RFC5804) to create/edit Sieve scripts (RFC5228). Used by [Cypht Webmail](https://cypht.org) and available to all PHP projects via [https://packagist.org/packages/henrique-borba/php-sieve-manager](https://packagist.org/packages/henrique-borba/php-sieve-manager)

[Tiki Wiki CMS Groupware bundles Cypht webmail](https://doc.tiki.org/Cypht) and [extends filters beyond what is possible via the Sieve protocol](https://doc.tiki.org/Email-filters).

[Compare php-sieve-manager to other options](https://github.com/cypht-org/php-sieve-manager/wiki/Comparison-of-Sieve-libs-in-PHP)

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

## Actions

[\[RFC5293\]](https://www.rfc-editor.org/rfc/rfc5293.html)

```
addheader [":last"]
    <field-name: string>
    <value: string>
```

[\[RFC5293\]](https://www.rfc-editor.org/rfc/rfc5293.html)

```
deleteheader [":index" <fieldno: number> [":last"]]
    [COMPARATOR] [MATCH-TYPE]
    <field-name: string>
    [<value-patterns: string-list>]
```

[\[RFC8580\]](https://www.rfc-editor.org/rfc/rfc8580.html) [\[RFC5435\]](https://www.rfc-editor.org/rfc/rfc5434.html)

```
notify [":from" string]
    [":importance" <"1" / "2" / "3">]
    [":options" string-list]
    [":message" string]
    [:fcc "INBOX.Sent"]
    <method: string>
```

[\[RFC5230\]](https://www.rfc-editor.org/rfc/rfc5230.html) [\[RFC6131\]](https://www.rfc-editor.org/rfc/rfc6131.html) [\[RFC8580\]](https://www.rfc-editor.org/rfc/rfc8580.html)

```
vacation [[":days" number] | [":seconds"]]
    [":subject" string]
    [":from" string]
    [":addresses" string-list]
    [":mime"]
    [":handle" string]
    <reason: string>
```

[\[RFC5232\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
setflag [<variablename: string>]
    <list-of-flags: string-list>
```

[\[RFC5232\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
addflag [<variablename: string>]
    <list-of-flags: string-list>
```

[\[RFC5232\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
removeflag [<variablename: string>]
    <list-of-flags: string-list>
```

[\[RFC5703\]](https://www.rfc-editor.org/rfc/rfc5703.html)

```
replace [":mime"]
    [":subject" string]
    [":from" string]
    <replacement: string>
```

[\[RFC5703\]](https://www.rfc-editor.org/rfc/rfc5703.html)

```
enclose <:subject string>
    <:headers string-list>
    string
```

[\[RFC5229\]](https://www.rfc-editor.org/rfc/rfc5229.html)

```
extracttext [MODIFIER]
    [":first" number]
    <varname: string>
```

[\[RFC6558\]](https://www.rfc-editor.org/rfc/rfc6558.html)

```
convert  <quoted-from-media-type: string>
    <quoted-to-media-type: string>
    <transcoding-params: string-list>
```

[\[RFC5229\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
set [MODIFIER] <name: string>
    <value: string>

Modifiers:  ":lower" / ":upper" / ":lowerfirst" / ":upperfirst" /
           ":quotewildcard" / ":length"
```

[\[RFC5232\]](https://www.rfc-editor.org/rfc/rfc5232.html) [\[RFC3894\]](https://www.rfc-editor.org/rfc/rfc3894.html) [\[RFC5228\]](https://www.rfc-editor.org/rfc/rfc5228.html) [\[RFC5490\]](https://www.rfc-editor.org/rfc/rfc5490.html) [\[RFC9042\]](https://www.rfc-editor.org/rfc/rfc9042.html) [\[RFC8579\]](https://www.rfc-editor.org/rfc/rfc8579.html)

```
fileinto [:mailboxid <mailboxid: string>] [:specialuse <special-use-attr: string>] [:create] [":copy"] [":flags" <list-of-flags: string-list>] <mailbox: string>
```

[\[RFC5228\]](https://www.rfc-editor.org/rfc/rfc5232.html) [\[RFC3894\]](https://www.rfc-editor.org/rfc/rfc3894.html) [\[RFC6009\]](https://www.rfc-editor.org/rfc/rfc6009.html)

```
redirect [":copy"] [:notify "value"] [:ret "FULL"|"HDRS"] [":copy"] <address: string>
```

[\[RFC5228\]](https://www.rfc-editor.org/rfc/rfc5232.html) [\[RFC5232\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
keep [":flags" <list-of-flags: string-list>]
```

[\[RFC5228\]](https://www.rfc-editor.org/rfc/rfc5232.html)

```
discard
```

[\[RFC5429\]](https://www.rfc-editor.org/rfc/rfc5429.html)

```
reject
```

[\[RFC5429\]](https://www.rfc-editor.org/rfc/rfc5429.html)

```
ereject
```
