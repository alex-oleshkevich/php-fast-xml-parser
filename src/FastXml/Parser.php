<?php

namespace FastXml;

use Exception;
use FastXml\CallbackHandler\CallbackHandlerInterface;
use FastXml\CallbackHandler\GenericHandler;

class Parser
{
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
    protected $ignoreTags = array();

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
     * Defines how much bytes to read from file per iteration.
     * 
     * @var int
     */
    protected $readBuffer = 8192;

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
        xml_set_external_entity_ref_handler($this->parser, 'convertEntities');
    }
    
    /**
     * Set option to XML parser.
     * 
     * @param int $name
     * @param mixed $value
     * @see XML_OPTION_* constants
     * @link http://php.net/manual/en/function.xml-parser-set-option.php
     * @return Parser
     */
    public function setOption($name, $value)
    {
        xml_parser_set_option($this->parser, $name, $value);
        return $this;
    }
    
    /**
     * Get option from XML parser.
     * 
     * @param int $name
     * @see XML_OPTION_* constants
     * @link http://php.net/manual/en/function.xml-parser-set-option.php
     * @return mixed
     */
    public function getParserOption($name)
    {
        return xml_parser_get_option($this->parser, $name);
    }
    
    /**
     * @return int
     */
    public function getReadBuffer()
    {
        return $this->readBuffer;
    }

    /**
     * @param int $readBuffer
     * @return Parser
     */
    public function setReadBuffer($readBuffer)
    {
        $this->readBuffer = $readBuffer;
        return $this;
    }

    /**
     * Do not include these tags into result.
     * 
     * @param array $tags
     */
    public function setIgnoreTags(array $tags)
    {
        $this->ignoreTags = $tags;
    }

    /**
     * Sets end tag.
     * 
     * End tag is a tag which is used to determine separate blocks.
     * @param string $tag
     */
    public function setEndTag($tag)
    {
        $this->endTag = $tag;
    }

    /**
     * Handles start tag.
     * 
     * @param resource $parser
     * @param string $name
     * @return null
     */
    public function startTag($parser, $name)
    {
        if (in_array($name, $this->ignoreTags)) {
            $this->currentTag = null;
            return;
        }
        $this->currentTag = $name;
    }

    /**
     * Handles tag content.
     * 
     * @param resource $parser
     * @param string $data
     */
    public function tagData($parser, $data)
    {
        if ($this->currentTag) {
            if (!isset($this->currentData[$this->currentTag])) {
                $this->currentData[$this->currentTag] = '';
            }
            $this->currentData[$this->currentTag] .= trim($data);
        }
    }

    /**
     * Handles close tag.
     * 
     * @param resource $parser
     * @param string $name
     */
    public function endTag($parser, $name)
    {
        if ($name == $this->endTag) {
            $this->callbackHandler->onItemParsed($this->currentData);
            $this->currentData = array();
        }
    }
    
    /**
     * Replaces all html entities into its original symbols.
     * 
     * @param string $content
     * @return string
     */
    public function convertEntities($content)
    {
        $table = array_map('utf8_encode', array_flip(
            array_diff(
                get_html_translation_table(HTML_ENTITIES), 
                get_html_translation_table(HTML_SPECIALCHARS)
            )
        ));
        return preg_replace('/&#[\d\w]+;/', '', strtr($content, $table));
    }

    /**
     * Do parsing.
     * 
     * @throws Exception
     */
    public function parse($file)
    {
        $handle = fopen($file, 'r');
        if (!$handle) {
            throw new Exception('Unable to open file.');
        }

        while (!feof($handle)) {
            $data = fread($handle, $this->readBuffer);
            xml_parse($this->parser, $data, feof($handle));
            $this->callbackHandler->onProgress(ftell($handle), filesize($file));
        }
    }

}
