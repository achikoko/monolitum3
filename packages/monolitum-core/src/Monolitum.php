<?php

namespace monolitum\core;

use Exception;
use monolitum\core\panic\BreakExecution;
use monolitum\core\panic\DevPanic;
use monolitum\core\panic\Panic;
use SplStack;

class Monolitum
{
    private static Monolitum $instance;
    private Panic|Exception|null $lastPanic;

    public static function getInstance(): Monolitum
    {
        if(!isset(self::$instance))
            self::$instance = new Monolitum();
        return self::$instance;
    }

    private bool $running = false;

    /**
     * @var SplStack<MNode>
     */
    private SplStack $buildingStack;

    public function __construct()
    {
        $this->buildingStack = new SplStack();
    }

    public function notifyStartBuilding(MNode $node): void
    {
        $this->buildingStack->push($node);
    }

    public function notifyEndBuilding(MNode $node): void
    {
        $popped = $this->buildingStack->pop();//array_pop($this->buildingStack);
        assert($popped === $node, "Popped wrong node.");
    }

    public function notifyStartExecuting(MNode $node): void
    {
        $this->buildingStack->push($node);
    }

    public function notifyEndExecuting(MNode $node): void
    {
        $popped = $this->buildingStack->pop();//array_pop($this->buildingStack);
        assert($popped === $node, "Popped wrong node.");
    }

    public function run(MNode $node): void
    {
        if($this->running)
            throw new DevPanic("Monolitum is already running.");
        $this->running = true;

        try {
            $node->doBuild();
            $node->doExecute();
        } catch (BreakExecution $ignored){
            // The execution is broken because someone has decided.
        } finally {
            $this->running = false;
        }
    }

    public function push(MObject $object): void
    {
        $this->pushFrom($object, null);
    }

    public function pushFrom(MObject $object, ?MNode $from): void
    {

        if($from === null){
            $node = $this->buildingStack->top();//[sizeof($this->buildingStack)-1];
        }else{
            $node = $from;
        }

        do{
            if($node->doReceive($object)){
                if ($node instanceof Find){
                    $this->getCurrentBuildingNode()->onFindIsResolved($node);
                }
                return;
            }
            $node = $node->getParent();
        }while($node != null);

        $object->onNotReceived();

    }

    public function setPanic(Exception|Panic $panic): void
    {
        $this->lastPanic = $panic;
    }

    public function getLastPanic(): Exception|Panic|null
    {
        return $this->lastPanic;
    }

    public function getCurrentBuildingNode(): MNode
    {
        return $this->buildingStack->top();//[sizeof($this->buildingStack) - 1];
    }

    public static function execute(MNode $node): void
    {
        self::getInstance() ->run($node);
        // TODO: check for panics
    }

}
