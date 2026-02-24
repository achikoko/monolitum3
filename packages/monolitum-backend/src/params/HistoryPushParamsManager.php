<?php

namespace monolitum\backend\params;

use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\model\attr\Attr;
use monolitum\model\Model;

class HistoryPushParamsManager extends MNode
{

    /**
     * @var array<string, Model>
     */
    private array $pushParameters;

    public function addPushParameters_everythingFromModel(Model|string $class): void
    {
        $model = Model::pushFindByName($class);
        foreach ($model->getAttrs() as $attr) {
            $this->pushParameters[$attr->getId()] = $model;
        }
    }

    public function addPushParameter(Model|string $class, Attr|string $attr): void
    {
        $model = Model::pushFindByName($class);
        $this->pushParameters[$model->getAttr($attr)->getId()] = $model;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof Request_MakeUrlString) {

            $object->addPushedParams($this->pushParameters);

            return parent::doAcceptChild($object);
//        }else if($object instanceof Active_Make_Url) {
//
//            $newActive = new Active_Make_Url_WithPushParameters($active->getLink(), $active->isObtainParamsAlone());
//            $newActive->setWriteAsParam($active->getWriteAsParam());
//            $newActive->addPushedParams($this->pushParameters);
//
//            GlobalContext::add($newActive, $this->getParent());
//
//            $active->setUrl($newActive->getUrl());
//            $active->setAloneParamValues($newActive->getAloneParamValues());
//
//            return true;
        }

        return parent::doAcceptChild($object);
    }

//    protected function receiveActive(Active $active): bool
//    {
//        if($active instanceof Active_Make_Url_WithPushParameters) {
//
//            $active->addPushedParams($this->pushParameters);
//
//            return parent::receiveActive($active);
//        }else if($active instanceof Active_Make_Url) {
//
//            $newActive = new Active_Make_Url_WithPushParameters($active->getLink(), $active->isObtainParamsAlone());
//            $newActive->setWriteAsParam($active->getWriteAsParam());
//            $newActive->addPushedParams($this->pushParameters);
//
//            GlobalContext::add($newActive, $this->getParent());
//
//            $active->setUrl($newActive->getUrl());
//            $active->setAloneParamValues($newActive->getAloneParamValues());
//
//            return true;
//        }
//
//        return parent::receiveActive($active);
//    }

//    /**
//     * @param callable|null $builder
//     */
//    public static function add($builder)
//    {
//        GlobalContext::add(new HistoryPushParamsManager($builder));
//    }

}
