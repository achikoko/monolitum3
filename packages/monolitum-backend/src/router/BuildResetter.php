<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\backend\globals\GlobalsManager;
use monolitum\core\MNode;

class BuildResetter extends MNode
{

    private int $iteration = 0;
    private bool $reset = false;

    function __construct(private readonly ?Closure $builder){
        parent::__construct();
    }

    protected function onBuild(): void
    {
        $builder = $this->builder;

        while (true){
            $this->reset = false;
            $idsBreakPoint = GlobalsManager::pushCreateBreakPoint();

            try {

                $builder($this);

                if(!$this->reset){
                    break;
                }

            }catch (ResetBuildPanic $exception){

            }

            $this->clearChildren();
            GlobalsManager::pushRestoreBreakPoint($idsBreakPoint);
            $this->iteration++;

        }

    }

    public static function throwReset(): void
    {
        throw new ResetBuildPanic();
    }

    public static function pushReset(): void
    {
        BuildResetter::findSelf()->reset = true;
    }

    public static function pushGetIteration(): int
    {
        return BuildResetter::findSelf()->iteration;
    }

}

