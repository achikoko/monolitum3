<?php

namespace monolitum\bootstrap\style;

use monolitum\core\GlobalContext;
use monolitum\frontend\ElementComponent_Ext;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;

class BSShadow extends HtmlElementNodeExtension implements BSBuiltIntoInterface
{

    public function __construct(private readonly bool $none, private readonly ?string $value)
    {
        parent::__construct();
    }

    public static function small(): static
    {
        return new self(false, "sm");
    }

    public static function regular(): static {
        return new self(false, null);
    }

    public static function large(): static {
        return new self(false, "lg");
    }

    public static function none(): static {
        return new self(true, null);
    }

    public function apply(): void
    {
        $this->buildInto($this->getElementComponent());
    }

    public function buildInto(HtmlElementNode $component, bool $inverted = false): void
    {

        if($this->none) {
            $this->getElementComponent()->setClass("bs_shadow", "shadow-none");
        }else{
            if($this->value !== null)
                $this->getElementComponent()->setClass("bs_shadow", "shadow-" . $this->value);
            else
                $this->getElementComponent()->setClass("bs_shadow", "shadow");
        }
    }
}
