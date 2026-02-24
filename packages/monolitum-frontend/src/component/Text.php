<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\frontend\ConstructFromContentTrait;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;

class Text extends Renderable_Node
{
    use ConstructFromContentTrait;

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
