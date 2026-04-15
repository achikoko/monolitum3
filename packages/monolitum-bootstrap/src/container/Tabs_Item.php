<?php

namespace monolitum\bootstrap\container;

use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;

class Tabs_Item implements MObject
{

    private string|TS|HtmlElementNode $body;

    private bool $open = false;
    private string $title;

    public function title(string|TS $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function body(HtmlElementNode|string|TS $body): self
    {
        $this->body = $body;
        return $this;
    }

    public function open(bool $open): self
    {
        $this->open = $open;
        return $this;
    }

    public function getTitle(): string|TS
    {
        return $this->title;
    }

    public function getBody(): HtmlElementNode|string|TS
    {
        return $this->body;
    }

    /**
     * @return bool
     */
    public function isOpen(): bool
    {
        return $this->open;
    }

    /**
     * @param string|TS|HtmlElementNode $body
     * @param bool $open
     * @return Tabs_Item
     */
    public static function of(
        HtmlElementNode|string|TS $body,
        bool $open = false
    ): Tabs_Item
    {
        $item = new Tabs_Item();
        $item->body($body);
        $item->open($open);
        return $item;
    }

    function onNotReceived()
    {
        throw new DevPanic("Accordion not found.");
    }

}
