<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\core\MNode;
use monolitum\core\Monolitum;
use monolitum\core\panic\NodePanicRouter;

class PanicRouter extends AbstractInstanceOfRouter implements NodePanicRouter
{

    private ?MNode $selected;

    function __construct(?Closure $builder){
        parent::__construct($builder);
    }

    /**
     * @param string $panicClass
     * @param callable|MNode $router
     */
    public function setRouteForPanic(string $panicClass, callable|MNode $router): self
    {
        $this->map[$panicClass] = $router;
        return $this;
    }

    protected function onBuild(): void
    {
        parent::onBuild();

        $panic = Monolitum::getInstance()->getLastPanic();

        $this->selected = $this->select(get_class($panic));

        if($this->selected == null){
            throw $panic;
        }else{
            if($this->selected instanceof MNode){
                $this->buildAndAppendChild($this->selected);
            }else if(is_callable($this->selected)){
                $c = $this->selected;
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

