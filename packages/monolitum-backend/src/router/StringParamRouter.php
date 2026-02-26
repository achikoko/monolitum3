<?php

namespace monolitum\backend\router;

use monolitum\backend\params\ValidatedValueGetter;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;

class StringParamRouter extends AbstractConstantRouter {

    private ?ValidatedValueGetter $param = null;

    /**
     * @var callable
     */
    private $onSelected;

    /**
     * @param ValidatedValueGetter $param
     * @param callable|null $builder
     */
    function __construct(callable $builder = null){
        parent::__construct($builder);
    }

    /**
     * @param ValidatedValueGetter $param
     */
    public function setParam(ValidatedValueGetter $param): void
    {
        $this->param = $param;
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

        if($this->param == null)
            throw new DevPanic("Param must be specified in StringParamRouter");

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
