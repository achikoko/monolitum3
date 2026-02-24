<?php

namespace monolitum\bootstrap\form;

use monolitum\frontend\HtmlElementNodeExtension;

class BSFormInputGroup extends HtmlElementNodeExtension
{

    public static function of(): BSFormInputGroup
    {
        return new BSFormInputGroup();
    }

    public function apply(): void
    {
        parent::apply();

        $elementComponent = $this->getElementComponent();
        $elementComponent->addClass("input-group");

    }

}
