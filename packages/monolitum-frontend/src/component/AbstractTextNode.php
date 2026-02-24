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

    /**
     * @param string|TS $text
     */
    public function appendText(string|TS $text): void
    {
        $this->append(new HtmlElementContent(TS::unwrap($text)));
    }

}
