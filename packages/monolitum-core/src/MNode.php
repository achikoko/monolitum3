<?php

namespace monolitum\core;

use Closure;
use monolitum\core\panic\DevPanic;
use monolitum\core\panic\NodePanicRouter;
use monolitum\core\panic\Panic;
use monolitum\core\util\ListUtils;

class MNode implements MObject
{

    private ?MNode $parent;

    private bool $building = false;

    private bool $built = false;

    private bool $panicked = false;

    private (MNode&NodePanicRouter)|null $panicRouter = null;

    /**
     * @var array<class-string, MObject>
     */
    private array $cachedByClassName = [];

    /**
     * @var array<class-string, MObject|Closure>
     */
    private array $interceptedFinds = [];

    /**
     * @var array<class-string, Closure>
     */
    private array $interceptedObjectProcessors = [];

    /**
     * @var array<MObject>
     */
    private array $children = [];

    public function __construct(private readonly ?Closure $builder = null)
    {

    }

    public function getParent(): ?MNode
    {
        return $this->parent;
    }

    /**
     * @return array<MObject>
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @param (MNode&NodePanicRouter)|null $panicRouter
     */
    public function setPanicRouter((MNode & NodePanicRouter)|null $panicRouter): void
    {
        $this->panicRouter = $panicRouter;
    }

    /**
     * Make this node to intercept a given class and return a harcoded value.
     * @param class-string $findingObjectClass
     * @param MObject|Closure $objectOrProvider
     * @return $this
     */
    public function interceptFind(string $findingObjectClass, MObject|Closure $objectOrProvider): self
    {
        $this->interceptedFinds[$findingObjectClass] = $objectOrProvider;
        return $this;
    }

    /**
     * Make this node to intercept a given class and return a harcoded value.
     * @param class-string $objectClass
     * @param Closure $reaction
     * @return $this
     */
    public function interceptReceive(string $objectClass, Closure $reaction): self
    {
        $this->interceptedObjectProcessors[$objectClass] = $reaction;
        return $this;
    }

    public function receive(MObject $object): void
    {
        Monolitum::getInstance()->pushFrom($object, $this);
    }

    function onNotReceived()
    {
        throw new DevPanic("Component " . $this . " was not received by any parent when added.");
    }

    function doBuild(MNode $parent = null): void
    {
        if($this->built)
            return;

        assert(!($this->building));

        $this->parent = $parent;

        $this->building = true;

        Monolitum::getInstance()->notifyStartBuilding($this);

        try{
            $this->onBuild();
            $this->onAfterBuild();
            $this->built = true;
        }catch (Panic $panic){
            Monolitum::getInstance()->setPanic($panic);
            $this->panicked = true;
            if($this->panicRouter != null){
                $this->panicRouter->doBuild($this);
            }else{
                throw $panic;
            }
        }finally{
            Monolitum::getInstance()->notifyEndBuilding($this);
        }

    }

    function doExecute(): void
    {

        assert($this->built || $this->panicked);

        Monolitum::getInstance()->notifyStartExecuting($this);

        if($this->panicked){
            assert($this->panicRouter != null);
            $this->panicRouter->doExecute();
        }else{
            $this->onExecute();
        }
        $this->onAfterExecute();

        Monolitum::getInstance()->notifyEndExecuting($this);

    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Find){
            if($this instanceof $object->class){
                $object->respond($this);
                return true;
            }else{
                if(
                    isset($this->interceptedFinds[$object->class])
                    || array_key_exists($object->class, $this->interceptedFinds)
                ){
                    // find in intercepted
                    $reaction = $this->interceptedFinds[$object->class];
                    if(is_callable($reaction)){
                        $result = $reaction($object);
                        $object->respond($result, true);
                    }else{
                        $object->respond($reaction, true);
                    }
                    return true; // not cache

                }else if(
                    isset($this->cachedByClassName[$object->class])
                    || array_key_exists($object->class, $this->cachedByClassName)
                ){
                    // find in cache
                    $object->respond($this->cachedByClassName[$object->class], true);
                    return true; // not (re)cache
                }
            }
        }else{
            $classOf = get_class($object);
            if(
                isset($this->interceptedObjectProcessors[$classOf])
                || array_key_exists($classOf, $this->interceptedObjectProcessors)
            ){
                // find in intercepted
                $reaction = $this->interceptedObjectProcessors[$classOf];
                if(is_callable($reaction)){
                    return $reaction($object);
                }else{
                    throw new DevPanic("Intercepted Active Class $classOf is not a callable.");
                }

            }else {
                // Try to accept
                return $this->doAcceptChild($object);
            }
        }

        return false;
    }

    function onFindIsResolved(Find $find): void
    {
        if (!$find->isFromCache() && $find->wantsToCache){
            $this->cachedByClassName[$find->class] = $find->getResponded();
        }
    }

    /**
     * Called when a node is received. You must call explicitly to buildAndAppendChild() to accept it and return true.
     * Or return false to refuse it.
     * @param MNode $object
     * @return bool
     */
    function doAcceptChild(MObject $object): bool
    {
        if($object instanceof NodePanicRouter){
            /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
            $this->panicRouter = $object;
            return true;
        }else if($object instanceof MNode && !($object instanceof ExplicitAcceptChildNode)){
            $this->buildAndAppendChild($object);
            return true;
        }

        return false;
    }

    /**
     * This method builds, configures, panics.
     */
    protected function onBuild(): void
    {
        // Build all children that has been added before building this
        foreach ($this->children as $child){
            if($child instanceof MNode){
                $child->doBuild($this);
            }
        }
        $this->runBuilderIfPresent();
    }

    /**
     * This method cannot panic.
     */
    protected function onExecute(): void
    {
        foreach ($this->children as $child){
            if($child instanceof MNode){
                $child->doExecute();
            }
        }
    }

    /**
     * @return void
     */
    public function runBuilderIfPresent(): void
    {
        if ($this->builder != null) {
            $b = $this->builder;
            $b($this);
        }
    }

    /**
     * This method is called after build, to prepare before executing.
     * It may panic if the previous build was illegal.
     */
    protected function onAfterBuild(): void{

    }

    /**
     * This method is called after execute, for some renderables to render, wink wink.
     */
    protected function onAfterExecute(): void{

    }

    protected function buildChildManually(MObject $object): void
    {
        if($object instanceof MNode){
            if ($object->built && $object->parent !== $this)
                throw new DevPanic("Cannot add component " . $object . " to " . $this . " because it has already a parent: " . $object->parent);
            if ($this->building){
                $object->doBuild($this);
            }
        }
    }

    protected function buildAndAppendChild(MObject $object): void
    {
        if($object instanceof MNode){
            if ($object->built && $object->parent !== $this)
                throw new DevPanic("Cannot add component " . $object . " to " . $this . " because it has already a parent: " . $object->parent);
            if ($this->building){
                $object->doBuild($this);
            }
        }
        $this->children[] = $object;
    }

    protected function buildAndInsertChild(MObject $object, ?int $idx = null): void
    {
        if($object instanceof MNode){
            if ($object->built && $object->parent !== $this)
                throw new DevPanic("Cannot add child " . $object . " to " . $this . " because it has already a parent: " . $object->parent);
            if ($this->building){
                $object->doBuild($this);
            }
        }
        ListUtils::insertAnElementIntoAnArray($this->children, $object, $idx);
    }

    protected function executeChildManually(MObject $object): void
    {
        // TODO Check: To execute a child manually, it must be built manually as well.
        if(!$this->built)
            throw new DevPanic("Cannot execute child " . $object . " because we are not built.");
        if($object instanceof MNode){
            if (!$object->built)
                throw new DevPanic("Cannot execute child " . $object . " because is not built.");
            if($object->parent !== $this)
                throw new DevPanic("Cannot execute child " . $object . " because it is not our child. Its parent is: " . $object->parent);
            $object->doExecute($this);
        }
    }

    public function pushSelf(): self
    {
        Monolitum::getInstance()->push($this);
        return $this;
    }

    public function pushSelfFrom(MNode $node): self
    {
        Monolitum::getInstance()->pushFrom($this, $node);
        return $this;
    }

    public static function findSelf(): static
    {
        return Find::pushAndGet(static::class);
    }

    public function __toString()
    {
        return get_class($this) . "()";
    }

}
