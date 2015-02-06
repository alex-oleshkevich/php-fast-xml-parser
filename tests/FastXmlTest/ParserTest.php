<?php

namespace FastXmlTest;

use FastXml\CallbackHandler\GenericHandler;
use FastXml\Parser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{

    public function testParser()
    {
        $file = __DIR__ . '/sample.xml';

        $self = $this;
        $iteration = 1;
        $handler = new GenericHandler;
        $handler->setOnItemParsedCallback(function ($item) use ($self, &$iteration) {
            $self->assertEquals('VALUE ' . $iteration, $item['value']);
            $iteration++;
        });
        $handler->setOnProgressCallback(function ($bytesProcessed, $bytesTotal) use ($self, $file) {
            $self->assertEquals(filesize($file), $bytesProcessed);
            $self->assertEquals(filesize($file), $bytesTotal);
        });
        $parser = new Parser($handler);
        $parser->setIgnoreTags(['root']);
        $parser->setEndTag('value');
        $parser->parse($file);
    }

    public function testParserSkipsTags()
    {
        $file = __DIR__ . '/sample2.xml';

        $self = $this;
        $iteration = 1;
        $handler = new GenericHandler;
        $handler->setOnItemParsedCallback(function ($item) use ($self, &$iteration) {
            $this->assertArrayNotHasKey('invalid', $item);
            $iteration++;
        });
        $parser = new Parser($handler);
        $parser->setIgnoreTags(['root', 'invalid']);
        $parser->setEndTag('content');
        $parser->parse($file);
    }

    public function testParserReportsOnProgress()
    {
        $file = __DIR__ . '/sample2.xml';

        $self = $this;
        $handler = new GenericHandler;
        $handler->setOnProgressCallback(function ($bytesProcessed, $bytesTotal) use ($self) {
            $this->assertContains($bytesProcessed, array(100, 200, 300, 363));
        });
        $parser = new Parser($handler);
        $parser->setReadBuffer(100);
        $parser->setIgnoreTags(['root']);
        $parser->setEndTag('content');
        $parser->parse($file);
    }

}
