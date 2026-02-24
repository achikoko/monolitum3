<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\BreakExecution;

class RedirectManager extends MNode
{

    private ?Link $redirectLink = null;

    private ?Request_SetResourceData $resourceData = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_SetRedirectPath){
            $activePath = $object->linkOrPath;
            if($activePath instanceof Path){
                if(!$this->redirectLink)
                    $this->redirectLink = new Link();
                $this->redirectLink->setPath($activePath);
            }else{
                $this->redirectLink = $activePath;
            }
            $this->resourceData = null;

            return true;
        }else if($object instanceof Request_SetResourceData){
            $this->resourceData = $object;
            $this->redirectLink = null;

            return true;
        }

        return parent::doReceive($object);
    }

    /**
     * @throws BreakExecution
     */
    protected function onExecute(): void
    {
        if($this->redirectLink !== null){

            $makeUrlString = new Request_MakeUrlString($this->redirectLink);
            Monolitum::getInstance()->push($makeUrlString);

            $url = $makeUrlString->getUrl();

            header("HTTP/1.1 303 See Other");
            header("Location: " . $url);

            // NOTE: Execution is finished here
            throw new BreakExecution();

        }else if($this->resourceData !== null){

            $base64Data = $this->resourceData->dataBase64;
            if($base64Data !== null){
                header('Content-Type: ' . "application/octet-stream");
                echo $base64Data;
            }else{
                $callable = $this->resourceData->writerFunction;
                if(is_callable($callable)){
                    header('Content-Type: ' . "application/octet-stream");
                    $callable();
                }
            }

            // NOTE: Execution is finished here
            throw new BreakExecution();

        }

        parent::onExecute();
    }

}
