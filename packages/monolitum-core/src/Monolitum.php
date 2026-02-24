<?php

namespace monolitum\core;

use Exception;
use monolitum\core\panic\BreakExecution;
use monolitum\core\panic\DevPanic;
use monolitum\core\panic\Panic;
use monolitum\core\util\ResourceAddressResolver;

class Monolitum
{
    private static Monolitum $instance;
    private Panic|Exception|null $lastPanic;

    private string $localAddress;
    private ?ResourceAddressResolver $resourceAddressResolver;

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

//        for ($i = sizeof($this->buildingStack)-1; $i >= 0; $i--) {
//            $node = $this->buildingStack[$i];
//            if($node->doReceive($object)){
//                if ($node instanceof Find){
//                    $this->getCurrentBuildingNode()->onFindIsResolved($node);
//                }
//                return;
//            }
//        }
//        $object->onNotReceived();
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

//        if($from === null){
//            $this->push($object);
//        }else{
//            $index = array_search($from, $this->buildingStack);
//            if($index === false){
//                throw new DevPanic("From node not found in build stack: $from");
//            }
//            for ($i = $index; $i >= 0; $i--) {
//                $node = $this->buildingStack[$i];
//                if($node->doReceive($object)){
//                    if ($node instanceof Find){
//                        $this->getCurrentBuildingNode()->onFindIsResolved($node);
//                    }
//                    return;
//                }
//            }
//            $object->onNotReceived();
//        }
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

    public function getLocalAddress(): string
    {
        return $this->localAddress;
    }

    public function getResourcesAddressResolver(): ResourceAddressResolver
    {
        return $this->resourceAddressResolver ?: ResourceAddressResolver::ofIdle();
    }

    public static function execute(string $localAddress, ?ResourceAddressResolver $resourcesAddressResolver, MNode $node): void
    {

        $monolitum = self::getInstance();
        $monolitum->localAddress = $localAddress;
        $monolitum->resourceAddressResolver = $resourcesAddressResolver;

        try{
            $monolitum->run($node);
        }catch (BreakExecution $ignored){

        }

        // TODO: check for panics

    }

}
