<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;

abstract class AbstractTextNode extends HtmlElementNode
{

    public function __construct($element, $builder = null)
    {
        parent::__construct($element, $builder);
    }

    public function appendText(string|TS|array $text, bool $raw = false): void
    {
        if ($raw){
            $this->append(new HtmlElementContent(TS::unwrapAuto($text), true));
        }else{
            $this->append(TS::renderAuto($text));
        }
    }

}
