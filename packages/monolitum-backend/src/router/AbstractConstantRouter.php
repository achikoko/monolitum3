<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\core\MNode;

abstract class AbstractConstantRouter extends AbstractMappedRouter{

    function __construct(?Closure $builder = null){
        parent::__construct($builder);
    }

    protected function select(mixed $value): MNode|Closure|null
    {

        if($value == null){
            if(array_key_exists("", $this->map))
                return $this->map[""];
            else if($this->defaultRoute !== null){
                return $this->defaultRoute;
            }else{
                return null;
            }
        }else if(array_key_exists($value, $this->map)){
            return $this->map[$value];
        }else{
            if($this->defaultRoute !== null){
                return $this->defaultRoute;
            }else{
                return null;
            }
        }

    }

}
