<?php

namespace monolitum\backend\resources;


use Closure;
use monolitum\backend\params\Request_MakeUrlString;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;

class HrefResolverManager extends MNode
{

    private string $host;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setHost(string $host): HrefResolverManager
    {
        $this->host = $host;
        return $this;
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
        if($param->isPrependHost){
            return $this->host . $active->getUrl();
        }else{
            return $active->getUrl();
        }
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_HrefResolver){
            if($object->isPrependHost() && $this->host === null){
                throw new DevPanic("Prepend host requested but not configured");
            }
            $object->setHrefResolver(new HrefResolver_Impl(
                $this, $object->link, $object->isSetParamsAlone(), $object->isPrependHost(),
                Monolitum::getInstance()->getCurrentBuildingNode()));
            return true;
        }

        return parent::doReceive($object);
    }

}
