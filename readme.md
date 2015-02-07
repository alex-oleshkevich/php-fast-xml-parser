PHP Fast XML Parser
=========

PHP Fast XML Parser is a PHP library for parsing large XML files using PHP.
Key features:

  - Lightweight;
  - Flexible (result can be easily managed via callback handlers);
  - Good for memory critical projects (~10Mb in average while parsing 500mb XML file)
  
![Build Status](https://travis-ci.org/alex-oleshkevich/php-fast-xml-parser.svg)

Example & Tutorial
--------------

```php

// create callback handler
$handler = new GenericHandler;

// set "on item parsed" callback
$handler->setOnItemParsedCallback(function ($item) use ($self) {
    // do smth with parsed item
});

// set "on progress" callback
$handler->setOnProgressCallback(function ($bytesProcessed, $bytesTotal) use ($self) {
    // eg. draw a progress bar
});

// instantiate
$parser = new Parser($handler);

// define tags which you don't want to include in resulting array (optional)
$parser->setIgnoreTags(['root']);

// define end tag for every item
// (this is used as marker to determine when XML
// item was processed.
// For example, if you want to extract "value" from this XML source
//<root>
//    <value>VALUE</value>
//    <value>VALUE</value>
//    <value>VALUE</value>
//</root>
// you must call $parser->setEndTag('value') so library can
// emit content of every <value /> tag in "onItemParsed" event.
$parser->setEndTag('value');

// run
$parser->parse('bigfile.xml');
```
