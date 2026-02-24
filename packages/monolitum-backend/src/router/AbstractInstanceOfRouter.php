<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;

abstract class AbstractInstanceOfRouter extends AbstractMappedRouter implements MObject {

    /**
     * @param callable|null $builder
     */
    function __construct(?Closure $builder = null){
        parent::__construct($builder);
    }

    protected function select(string $class): ?MNode
    {
        if($class != null && array_key_exists($class, $this->map)){
            return $this->map[$class];
        }else{
            foreach ($this->map as $item => $value) {
                if(is_subclass_of($class, $item))
                    return $value;
            }
        }
        return $this->defaultRoute;
    }

}
