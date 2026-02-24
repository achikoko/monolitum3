<?php

namespace monolitum\frontend\component;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

class H extends AbstractTextNode
{

    public function __construct($level = 1, $builder = null)
    {
        parent::__construct(new HtmlElement("H" . $level), $builder);
    }

    /**
     * @param int $level
     * @param string|Renderable_Node|TS $content
     * @return H
     */
    public static function from(int $level, Renderable_Node|TS|string $content): H
    {
        $fc = new H($level);
        $fc->append($content);
        return $fc;
    }

}
