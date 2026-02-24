<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSOverflow extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    function __construct(private readonly string $value)
    {
        parent::__construct();
    }

    public static function auto(): static
    {
        return new self("top");
    }

    public static function hidden(): static{
        return new self("middle");
    }

    public static function visible(): static{
        return new self("bottom");
    }

    public static function scroll(): static{
        return new self("bottom");
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("overflow-" . $this->value);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}
