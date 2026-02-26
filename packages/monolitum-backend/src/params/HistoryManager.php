<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\model\Model;

class HistoryManager extends MNode
{

    /**
     * @var array<Link>
     */
    private array $linkStack = array();

    /**
     * @var bool|string
     */
    private string|bool $writeAsParam;

    /**
     * @var array<string, Model>
     */
    private array $pushParameters = [];

    public function __construct(public readonly ValidatedValueGetter $readPathParam, ?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setWriteAsParam(bool|string $paramName): self
    {
        $this->writeAsParam = $paramName;
        return $this;
    }

    /**
     * @param Link $fallbackLink
     * @return Link
     */
    public function getTopHistory(Link|Path $fallbackLink): Link
    {
        if($fallbackLink instanceof Path) {
            $fallbackLink = new Link($fallbackLink);
        }

        if(sizeof($this->linkStack) > 0){
            return $this->linkStack[count($this->linkStack) - 1];
        }else{
            return $fallbackLink;
        }
    }

    /**
     * @param class-string|Model $class
     */
    public function addDefaultPushParameters_everythingFromModel(Model|string $class): void
    {
        $model = Model::pushFindByName($class);
        foreach ($model->getAttrs() as $attr) {
            $this->pushParameters[$attr->getId()] = $model;
        }
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_MakeUrlString) {

            if($object->link instanceof Path){
                return parent::doReceive($object);
            }

            $copiedLink = null;

            $historyBehavior= $object->link->getHistoryBehavior();
            if($historyBehavior != null) {
                switch ($object->link->getHistoryBehavior()) {
                    case Link::HISTORY_BEHAVIOR_PRESERVE:
                        {
                            $copiedLink = $object->link->copy();
                            $copiedLink->removeParams($this->writeAsParam);
                            $hValue = $this->writeHistory($this->linkStack);
                            if (strlen($hValue) > 0) {
                                $copiedLink->addParams([
                                    $this->writeAsParam => $hValue,
                                ]);
                            }

                        }
                        break;
                    case Link::HISTORY_BEHAVIOR_PUSH:
                        {
                            $copiedLink = $object->link->copy();
                            $copiedLink->removeParams($this->writeAsParam);

                            $myPushParams = [];

                            foreach ($this->pushParameters as $paramId => $model) {
                                $paramValueActive = new Request_Parameter_ValidatedValue(null, $model, $paramId);
                                Monolitum::getInstance()->pushFrom($paramValueActive, $this->getParent());
                                $validatedValue = $paramValueActive->getValidatedValue();
                                if ($validatedValue->isValid() && !$validatedValue->isNull()) {
                                    $myPushParams[$paramId] = $validatedValue->getStrValue();
                                }
                            }

                            $pushedParams = $object->getPushedParams();

                            if ($pushedParams !== null) {
                                foreach ($pushedParams as $paramId => $model) {
                                    $paramValueActive = new Request_Parameter_ValidatedValue(Abstract_Request_ValidatedValue::TYPE_STRING, $model, $paramId);
                                    Monolitum::getInstance()->pushFrom($paramValueActive, $this->getParent());
                                    $validatedValue = $paramValueActive->getValidatedValue();
                                    if ($validatedValue->isValid() && !$validatedValue->isNull()) {
                                        $myPushParams[$paramId] = $validatedValue->getStrValue();
                                    }
                                }
                            }

                            $linkStackCopy = $this->linkStack;
                            $linkStackCopy[] = Link::from(Path::fromRelative())->addParams($myPushParams);

                            $hValue = $this->writeHistory($linkStackCopy);
                            if (strlen($hValue) > 0) {
                                $copiedLink->addParams([
                                    $this->writeAsParam => $hValue,
                                ]);
                            }

                        }
                        break;
                    case Link::HISTORY_BEHAVIOR_POP:
                        {

                            if (count($this->linkStack) > 0) {

                                // TODO check that path is equal to the backup link
                                $linkStackCopy = $this->linkStack;
                                $copiedLink = $linkStackCopy[sizeof($linkStackCopy) - 1]->copy();
                                unset($linkStackCopy[sizeof($linkStackCopy) - 1]);

                                $hValue = $this->writeHistory($linkStackCopy);
                                if (strlen($hValue) > 0) {
                                    $copiedLink->addParams([
                                        $this->writeAsParam => $hValue,
                                    ]);
                                }

                            } else {

                                $copiedLink = $object->link->copy();
                                $copiedLink->removeParams($this->writeAsParam);

                            }

                        }
                        break;

                }
            }

            if($copiedLink === null){
                $copiedLink = $object->link;
            }

            $newRequest = new Request_MakeUrlString($copiedLink, $object->obtainParamsAlone);
            $newRequest->setAloneParamValues($object->getAloneParamValues());
            $newRequest->setWriteAsParam($object->getWriteAsParam());

            Monolitum::getInstance()->pushFrom($newRequest, $this->getParent());

            $object->setUrl($newRequest->getUrl());
            $object->setAloneParamValues($newRequest->getAloneParamValues());

            return true;

        }

        return parent::doReceive($object);
    }

    protected function onBuild(): void
    {
        $validatedPath = $this->readPathParam->getValidatedValue();
        if($validatedPath->isValid() && !$validatedPath->isNull()){
            /** @var string $pathArrayStrStr */
            $pathArrayStrStr = $validatedPath->getValue();
            if(strlen($pathArrayStrStr) > 0){
                $pathArrayStr = explode(" ", $pathArrayStrStr);
                if($pathArrayStr !== false){
                    foreach($pathArrayStr as $pathStr){
                        if(strlen($pathStr) > 0){
                            $decoded = urldecode($pathStr);
                            $this->linkStack[] = Link::fromUrl($decoded);//Active_Transform_Url2Link::from($decoded)->go()->getLink();
                        }
                    }
                }
            }
        }

        parent::onBuild();
    }

    private function writeHistory(array $linkStack): string
    {
        if(count($linkStack) > 0){
            $string = "";
            $first = true;
            foreach($linkStack as $link){
                if(!$first){
                    $string .= " ";
                }
                $requestMakeUrl = new Request_MakeUrlString($link);
                $requestMakeUrl->setWriteAsParam(false);
                $requestMakeUrl->setAppendUrlPrefix(false);
                Monolitum::getInstance()->pushFrom($requestMakeUrl, $this->getParent());
                $string .= urlencode($requestMakeUrl->getUrl());

                $first = false;
            }

            return $string;
        }else{
            return "";
        }
    }

}
