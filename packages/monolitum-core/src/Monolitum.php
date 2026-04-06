<?php

namespace monolitum\core;

use Exception;
use monolitum\core\panic\BreakExecution;
use monolitum\core\panic\DevPanic;
use monolitum\core\panic\Panic;

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
     * @var array<MNode>
     */
    private array $buildingStack = array();

    public function notifyStartBuilding(MNode $node): void
    {
        $this->buildingStack[] = $node;
    }

    public function notifyEndBuilding(MNode $node): void
    {
        $popped = array_pop($this->buildingStack);
        assert($popped === $node, "Popped wrong node.");
    }

    public function notifyStartExecuting(MNode $node): void
    {
        $this->buildingStack[] = $node;
    }

    public function notifyEndExecuting(MNode $node): void
    {
        $poped = array_pop($this->buildingStack);
        assert($poped === $node, "Popped wrong node.");
    }

    public function run(MNode $node): void
    {
        if($this->running)
            throw new DevPanic("Monolitum is already running.");
        $this->running = true;

        try {
            $node->doBuild();
            $node->doExecute();
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
            $node = $this->buildingStack[sizeof($this->buildingStack)-1];
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
        return $this->buildingStack[sizeof($this->buildingStack) - 1];
    }

    public static function execute(MNode $node): void
    {

        $monolitum = self::getInstance();

        try{
            $monolitum->run($node);
        }catch (BreakExecution $ignored){

        }

        // TODO: check for panics

    }

}
