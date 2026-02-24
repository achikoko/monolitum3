<?php

namespace monolitum\bootstrap\container;

use monolitum\frontend\HtmlElementNode;

class Accordion_Item
{

    private string|HtmlElementNode $header;

    private string|HtmlElementNode $body;

    private bool $collapsed = true;

    public function header(HtmlElementNode|string $header): self
    {
        $this->header = $header;
        return $this;
    }

    public function body(HtmlElementNode|string $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function collapsed(bool $collapsed): self
    {
        $this->collapsed = $collapsed;
        return $this;
    }

    public function getBody(): HtmlElementNode|string
    {
        return $this->body;
    }

    public function getHeader(): HtmlElementNode|string
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
     * @param string|HtmlElementNode $header
     * @param string|HtmlElementNode $body
     * @param bool $collapsed
     * @return Accordion_Item
     */
    public static function of(
        HtmlElementNode|string $header,
        HtmlElementNode|string $body,
        bool $collapsed = true
    ): Accordion_Item
    {
        $item = new Accordion_Item();
        $item->header($header);
        $item->body($body);
        $item->collapsed($collapsed);
        return $item;
    }

}
