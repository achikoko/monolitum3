<?php

namespace monolitum\frontend;

use Closure;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\core\util\ListUtils;

/**
 * A reference is added and build normally to a parent. It forwards all its children build calls to the parent as manual build and then all children is rendered when the Reference is rendered.
 */
class Reference extends Renderable_Node
{

    private array $childrenOfParent = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    protected function buildChildManually(MObject $object): void
    {
        throw new DevPanic("Reference does not support manual build of children.");
    }

    protected function buildAndAppendChild(MObject $object): void
    {
        $this->getParent()->buildChildManually($object);
        $this->childrenOfParent[] = $object;
    }

    protected function buildAndInsertChild(MObject $object, ?int $idx = null): void
    {
        if($object instanceof Rendered){
            // It may contain children that has to be built
            $object->buildChildrenTo(function ($c) { $this->buildAndInsertChild($c); });
        }else{
            $this->getParent()->buildChildManually($object);
            ListUtils::insertAnElementIntoAnArray($this->childrenOfParent, $object, $idx);
        }
    }

    protected function onExecute(): void
    {
        foreach ($this->childrenOfParent as $child) {
            $this->getParent()->executeChildManually($child);
        }
    }

    public function render(): Renderable|array|null
    {
        return Rendered::of($this->childrenOfParent);
    }

    public static function of(?Closure $builder = null): static
    {
        return new Reference($builder);
    }

}
