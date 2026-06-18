<?php

namespace monolitum\frontend\component;

use monolitum\frontend\AppendTextTrait;
use monolitum\frontend\ConstructFromContentTrait;
use monolitum\frontend\Renderable_Node;

class Text extends Renderable_Node
{
    use ConstructFromContentTrait;
    use AppendTextTrait;

//    public function __construct(string|TS $string, ?Closure $builder = null)
//    {
//        parent::__construct($builder);
//        $this->append($string);
//    }

//    public static function of(string $name): static
//    {
//        return new static(function(Text $it) use ($name) {
//            $it->append($name);
//        });
//    }

}
