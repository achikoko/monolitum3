<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable_Node;

class P extends AbstractTextNode
{

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("p"), $builder);
    }

    /**
     * @param string|Renderable_Node $content
     * @return P
     */
    public static function from(string|Renderable_Node $content): P
    {
        $fc = new P();
        $fc->append($content);
        return $fc;

    }

}
