<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\core\MNode;
use monolitum\core\Monolitum;
use monolitum\core\panic\NodePanicRouter;

class PanicRouter extends AbstractInstanceOfRouter implements NodePanicRouter
{

    function __construct(?Closure $builder){
        parent::__construct($builder);
    }

    public function setRouteForPanic(string $panicClass, Closure|MNode $router): self
    {
        $this->map[$panicClass] = $router;
        return $this;
    }

    protected function onBuild(): void
    {
        parent::onBuild();

        $panic = Monolitum::getInstance()->getLastPanic();

        $selected = $this->select(get_class($panic));

        if($selected == null){
            throw $panic;
        }else{
            if($selected instanceof MNode){
                $this->buildAndAppendChild($selected);
            }else if(is_callable($selected)){
                $c = $selected;
                $c();
            }
        }

    }

//    protected function onExecute(): void
//    {
//        if($this->selected instanceof MNode){
//            $this->selected->doExecute();
//        }
//    }

}

