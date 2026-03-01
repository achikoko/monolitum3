<?php

namespace monolitum\bootstrap\container;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;

class Accordion_Item implements MObject
{

    private string|TS|HtmlElementNode $header;

    private string|TS|HtmlElementNode $body;

    private bool $collapsed = true;

    public function header(HtmlElementNode|string|TS $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function body(HtmlElementNode|string|TS $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function collapsed(bool $collapsed): self
    {
        $this->collapsed = $collapsed;
        return $this;
    }

    public function getBody(): HtmlElementNode|string|TS
    {
        return $this->body;
    }

    public function getHeader(): HtmlElementNode|string|TS
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    public function isCollapsed(): bool
    {
        return $this->collapsed;
    }

    /**
     * @param string|TS|HtmlElementNode $header
     * @param string|TS|HtmlElementNode $body
     * @param bool $collapsed
     * @return Accordion_Item
     */
    public static function of(
        HtmlElementNode|string|TS $header,
        HtmlElementNode|string|TS $body,
        bool $collapsed = true
    ): Accordion_Item
    {
        $item = new Accordion_Item();
        $item->header($header);
        $item->body($body);
        $item->collapsed($collapsed);
        return $item;
    }

    function onNotReceived()
    {
        throw new DevPanic("Accordion not found.");
    }
}
