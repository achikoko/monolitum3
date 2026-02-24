<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable_Node;

class Span extends AbstractTextNode
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("span"), $builder);
    }

    /**
     * @param string|Renderable_Node $content
     * @return Span
     */
    public static function from(string|Renderable_Node $content): Span
    {
        $fc = new Span();
        $fc->append($content);
        return $fc;
    }

}
