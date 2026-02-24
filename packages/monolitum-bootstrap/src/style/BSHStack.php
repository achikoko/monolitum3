<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSHStack extends HtmlElementNodeExtension
{

    private ?int $gap = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function gap(int $value): self
    {
        assert(0 <= $value && $value <= 5, "Gap should be between 0 and 5");
        $this->gap = $value;
        return $this;
    }

    public static function of(): static
    {
        return new BSHStack();
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {

        $component->addClass("hstack");

        if($this->gap !== null)
            $component->addClass("gap-" . $this->gap);

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}
