<?php
namespace FastXmlTest;

use FastXml\CallbackHandler\GenericHandler;
use FastXml\Parser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParser()
    {
        $self = $this;
        $handler = new GenericHandler;
        $handler->setOnItemParsedCallback(function ($item) use ($self) {
            $self->assertEquals('VALUE', $item['value']);
        });
        $handler->setOnProgressCallback(function ($bytesProcessed, $bytesTotal) use ($self) {
            $self->assertEquals(129, $bytesProcessed);
            $self->assertEquals(129, $bytesTotal);
        });
        $parser = new Parser($handler);
        $parser->setSkipTags(['root']);
        $parser->setEndTag('value');
        $parser->parse(__DIR__ . '/sample.xml');
    }
}