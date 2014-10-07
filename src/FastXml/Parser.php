<?php

namespace FastXml;

use Exception;
use FastXml\CallbackHandler\CallbackHandlerInterface;
use FastXml\CallbackHandler\GenericHandler;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Parser extends EventDispatcher
{
    const EVENT_PRODUCT_PARSED = 'feedparser.product_parsed';
    const EVENT_PROGRESS = 'feedparser.progress';
    const END_TAG = 'product';

    /**
     * XML parser resource.
     * @var resource
     */
    protected $parser;

    /**
     * Currently aggregated data.
     * @var array
     */
    protected $currentData = array();

    /**
     * @var string
     */
    protected $currentTag;

    /**
     * Tags to exclude from result
     * @var array
     */
    protected $skipTags = array();

    /**
     * Endpoint of XML item.
     * @var string
     */
    protected $endTag;

    /**
     * @var CallbackHandlerInterface
     */
    protected $callbackHandler;

    /**
     * Constructor.
     */
    public function __construct(CallbackHandlerInterface $callbackHandler = null)
    {
        if (null === $callbackHandler) {
            $callbackHandler = new GenericHandler;
        }
        $this->callbackHandler = $callbackHandler;

        $this->parser = xml_parser_create('UTF-8');
        xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($this->parser, $this);
        xml_set_element_handler($this->parser, 'startTag', 'endTag');
        xml_set_character_data_handler($this->parser, 'tagData');
    }

    /**
     * Do not include these tags into result.
     * @param array $tags
     */
    public function setSkipTags(array $tags)
    {
        $this->skipTags = $tags;
    }

    /**
     * Sets end tag.
     * End tag is a tag which is used to determine separate blocks.
     * @param string $tag
     */
    public function setEndTag($tag)
    {
        $this->endTag = $tag;
    }

    /**
     * Handles start tag.
     * @param resource $parser
     * @param string $name
     * @return null
     */
    public function startTag($parser, $name)
    {
        if (in_array($name, $this->skipTags)) {
            return;
        }
        $this->currentTag = $name;
    }

    /**
     * Handles tag content.
     * @param resource $parser
     * @param string $data
     */
    public function tagData($parser, $data)
    {
        if ($this->currentTag) {
            $this->currentData[$this->currentTag] = trim($data);
            $this->currentTag = null;
        }
    }

    /**
     * Handles close tag.
     * @param resource $parser
     * @param string $name
     */
    public function endTag($parser, $name)
    {
        if ($name == $this->endTag) {
            $this->callbackHandler->onItemParsed($this->currentData);
        }
    }

    /**
     * Do parsing.
     * @throws Exception
     */
    public function parse($file)
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception('Unable to open file.');
        }

        while (!feof($handle)) {
            $data = fread($handle, 8192);
            xml_parse($this->parser, $data, feof($handle));
            $this->callbackHandler->onProgress(ftell($handle), filesize($file));
        }
    }

}
