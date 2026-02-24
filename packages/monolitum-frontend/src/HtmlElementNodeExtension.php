<?php

namespace monolitum\frontend;

use Closure;
use monolitum\core\ExplicitAcceptChildNode;
use monolitum\core\MNode;

class HtmlElementNodeExtension extends MNode implements ExplicitAcceptChildNode
{

    private HtmlElementNode $elementComponent;
    private ?Closure $applier;

    function __construct(?Closure $builder = null, ?Closure $applier = null)
    {
        parent::__construct($builder);
        $this->applier = $applier;
    }

    function _setElementComponent(HtmlElementNode $elementComponent): void
    {
        $this->elementComponent = $elementComponent;
    }

    public function getElementComponent(): HtmlElementNode
    {
        return $this->elementComponent;
    }

    /**
     * @param string $classes
     * @return $this
     */
    public function addClass(string ...$classes): self
    {
        $this->elementComponent->addClass(...$classes);
        return $this;
    }

    /**
     * Sets a class with an alias, if this class is reset with the same alias, the previous class is removed
     * @param string $alias
     * @param string|null $class
     * @return $this
     */
    public function setClass(string $alias, string $class = null): self
    {
        $this->elementComponent->setClass($alias, $class);
        return $this;
    }

    function onNotReceived()
    {

    }

    public function apply(): void
    {
        if($this->applier !== null){
            $b = $this->applier;
            $b($this);
        }
    }
}
