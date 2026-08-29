<?php

namespace monolitum\frontend\component;

use monolitum\frontend\AppendTextTrait;
use monolitum\frontend\ConstructFromContentTrait;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

class Text extends Renderable_Node
{
    use ConstructFromContentTrait;
    use AppendTextTrait;

//    public function __construct(string|TS $string, ?Closure $builder = null)
//    {
//        parent::__construct($builder);
//        $this->append($string);
//    }

    public static function ofRichText(string|TS|array $richText): static
    {
        return new static(function(Text $it) use ($richText) {
            $it->appendRichText($richText);
        });
    }

}
