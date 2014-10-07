<?php
namespace FastXml\CallbackHandler;

interface CallbackHandlerInterface
{
    public function onProgress($bytesProcessed, $bytesTotal);
    public function onItemParsed(array $item);
}