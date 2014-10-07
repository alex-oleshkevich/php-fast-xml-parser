<?php

namespace FastXml\CallbackHandler;

use Closure;
use FastXml\CallbackHandler\CallbackHandlerInterface;

class GenericHandler implements CallbackHandlerInterface
{

    /**
     * @var Closure
     */
    protected $onProgressCallback;

    /**
     * @var Closure
     */
    protected $onItemParsedCallback;

    public function onItemParsed(array $item)
    {
        if (is_callable($this->onItemParsedCallback)) {
            $callback = $this->onItemParsedCallback;
            $callback($item);
        }
    }

    public function onProgress($bytesProcessed, $bytesTotal)
    {
        if (is_callable($this->onProgressCallback)) {
            $callback = $this->onProgressCallback;
            $callback($bytesProcessed, $bytesTotal);
        }
    }

    public function setOnProgressCallback(callable $callback)
    {
        $this->onProgressCallback = $callback;
    }

    public function setOnItemParsedCallback(callable $callback)
    {
        $this->onItemParsedCallback = $callback;
    }

}
