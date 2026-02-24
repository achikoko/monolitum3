<?php

namespace monolitum\backend\router;

use monolitum\backend\params\ValidatedValueGetter;
use monolitum\core\MNode;

class StringParamRouter extends AbstractConstantRouter {

    /**
     * @var callable
     */
    private $onSelected;

    /**
     * @param ValidatedValueGetter $param
     * @param callable|null $builder
     */
    function __construct(private readonly ValidatedValueGetter $param, callable $builder = null){
        parent::__construct($builder);
    }

    public function setOnSelected(callable $onSelected): self
    {
        $this->onSelected = $onSelected;
        return $this;
    }

    public function setRouteForValue(string $value, MNode|callable $router): self
    {
        $this->map[$value] = $router;
        return $this;
    }

    protected function onBuild(): void
    {
        parent::onBuild();

        $validatedValue = $this->param->getValidatedValue();

        $selected = null;
        if($validatedValue->isValid()){
            $selected = $this->select($validatedValue->getValue());
        }else{
            $selected = $this->select('');
        }

        if($selected == null){
            throw new Panic_NothingSelected();
        }else{

            if($selected instanceof MNode){
                // If managed to build, this child will remain into the children list, so it will be executed.
                $this->buildAndAppendChild($selected);
            }else if(is_callable($selected)){
                $c = $selected;
                $c();
            }

            if($this->onSelected != null){
                $s = $this->onSelected;
                $s($selected);
            }

        }

    }

}
