<?php

namespace monolitum\backend\params;

use monolitum\core\panic\DevPanic;
use monolitum\database\DatabaseManager;
use monolitum\model\Entity;
use monolitum\model\EntityPersister;
use monolitum\model\Model;

readonly class ParamsProvider_DatabaseGlobals_Persister implements EntityPersister
{

    public function __construct(
        private ParamsProvider_DatabaseGlobals $param,
        private Model                          $model
    ){

    }

    public function _notifyEntityChanged(Entity $entity): void
    {
        // TODO: Implement _notifyEntityChanged() method.
    }

    public function _executeInsertEntity(Entity $entity): array
    {
        throw new DevPanic("Not supported");
    }

    public function _executeUpdateEntity(Entity $entity): array
    {
        $prefix = '';
        if($this->param->getPrefixModelUnionString() && $this->model->id){
            $prefix = $this->model->id . $this->param->getPrefixModelUnionString();
        }

        foreach($entity->getUpdateAttrs() as $attrName => $value){
            $attr = $this->model->getAttr($attrName);
            DatabaseManager::findSelf()->newInsert($this->param->model)
                ->upsert()
                ->addValue($this->param->key, $prefix . $attrName)
                ->addValue($this->param->value, $attr->stringValue($value))
                ->execute();
        }
        return []; // Ignore it!
    }

    public function _executeDeleteEntity(Entity $entity): int
    {
        // TODO remove all parameters?
        throw new DevPanic("Not supported");
    }
}
