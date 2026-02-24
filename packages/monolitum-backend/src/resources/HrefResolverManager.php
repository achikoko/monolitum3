<?php

namespace monolitum\backend\resources;


use Closure;
use monolitum\backend\params\Request_MakeUrlString;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;

class HrefResolverManager extends MNode
{

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param HrefResolver_Impl $param
     * @return string
     */
    public function makeHref(HrefResolver_Impl $param): string
    {

        $active = new Request_MakeUrlString($param->link, $param->obtainParamsAlone);
        Monolitum::getInstance()->pushFrom($active, $param->callerNode);
        $param->setAloneParamValues($active->getAloneParamValues());
        return $active->getUrl();
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_HrefResolver){
            $object->setHrefResolver(new HrefResolver_Impl(
                $this, $object->link, $object->isSetParamsAlone(),
                Monolitum::getInstance()->getCurrentBuildingNode()));
            return true;
        }

        return parent::doReceive($object);
    }

}
