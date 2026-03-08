<?php

namespace monolitum\backend\globals;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;

class GlobalsManager extends MNode
{

    private int $uniqueId = 0;

    private array $uniqueIdByContext = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function doReceive(MObject $object): bool
    {
        if ($object instanceof Request_NewId){
            $this->processNewId($object);
            return true;
        }
        return parent::doReceive($object);
    }

    private function processNewId(Request_NewId $newId): void
    {

        if($newId->contextIds === null){

            $id = $this->uniqueId++;
            $newId->setId("uid_" . $id);

        }else{

            if(key_exists($newId->contextIds, $this->uniqueIdByContext)){
                $id = $this->uniqueIdByContext[$newId->contextIds]++;
            }else{
                $this->uniqueIdByContext[$newId->contextIds] = 1;
                $id = 0;
            }

            $newId->setId("uid_" . $newId->contextIds . "_" . $id);

        }
    }

    private function createBreakPoint(): GlobalsBreakPoint
    {
        return new GlobalsBreakPoint(
            $this->uniqueId,
            $this->uniqueIdByContext
        );
    }

    private function restoreBreakPoint(GlobalsBreakPoint $breakPoint): void
    {
        $this->uniqueId = $breakPoint->uniqueId;
        $this->uniqueIdByContext = $breakPoint->uniqueIdByContext;
    }

    public static function pushCreateBreakPoint(): GlobalsBreakPoint
    {
        return GlobalsManager::findSelf()->createBreakPoint();
    }

    public static function pushRestoreBreakPoint(GlobalsBreakPoint $breakPoint): void
    {
        GlobalsManager::findSelf()->restoreBreakPoint($breakPoint);
    }

}
