<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSVerticalAlign extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $value
     */
    function __construct($value)
    {
        parent::__construct();
        $this->value = $value;
    }

    public static function top(): static
    {
        return new static("top");
    }

    public static function middle(): static
    {
        return new static("middle");
    }

    public static function bottom(): static
    {
        return new static("bottom");
    }

    public function getValue(bool $inverted = false): string
    {
        return $this->value;
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("align-" . $this->value);
    }

}
