<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSRow extends HtmlElementNodeExtension
{

    private ?int $gx = null;
    private ?int $gy = null;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param int $value
     * @return $this
     */
    public function gx(int $value): self
    {
        assert(0 <= $value && $value <= 5, "Gutter should be between 0 and 5");
        $this->gx = $value;
        return $this;
    }

    /**
     * @param int $value
     * @return $this
     */
    public function gy(int $value): self
    {
        assert(0 <= $value && $value <= 5, "Gutter should be between 0 and 5");
        $this->gy = $value;
        return $this;
    }

    public static function of(): static
    {
        return new BSRow();
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {
        $component->addClass("row");

        if($this->gx !== null)
            $component->addClass("gx-" . $this->gx);

        if($this->gy !== null)
            $component->addClass("gap-" . $this->gy);

    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

}
