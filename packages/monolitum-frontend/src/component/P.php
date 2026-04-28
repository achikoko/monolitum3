<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

class P extends AbstractTextNode
{

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("p"), $builder);
    }

    /**
     * @param Renderable_Node|Renderable|string|TS|array|null $content
     * @return P
     */
    public static function from(Renderable_Node|Renderable|string|TS|array|null $content): P
    {
        $fc = new P();
        $fc->append($content);
        return $fc;

    }

}
