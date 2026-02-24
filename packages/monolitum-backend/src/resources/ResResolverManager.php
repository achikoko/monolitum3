<?php

namespace monolitum\backend\resources;

use Closure;
use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\backend\params\Request_MakeUrlString;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;

class ResResolverManager extends MNode
{

    private Path $writePath;

    private string $writeResourceParam;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setWritePath(Path $writePath): void
    {
        $this->writePath = $writePath;
    }

    public function setWriteResourceParam(string $writeResourceParam): void
    {
        $this->writeResourceParam = $writeResourceParam;
    }

    /**
     * @param ResResolver_Impl $param
     * @return string
     */
    public function makeRes(ResResolver_Impl $param): string
    {
        // p = res
        // r = path

        $url = $param->link->writePath($param->encodeUrl);

        $link = new Link($this->writePath);
        $link->setDontPreserveHistory();
        $link->addParams([
            $this->writeResourceParam => $url
        ]);

        $request = new Request_MakeUrlString($link);
        Monolitum::getInstance()->push($request);

        return $request->getUrl();
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_ResResolver){
            $object->setResResolver(new ResResolver_Impl($this, $object->getPath(), $object->isEncodeUrl()));
            return true;
        }

        return parent::doReceive($object);
    }

}
