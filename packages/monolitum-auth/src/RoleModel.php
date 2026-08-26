<?php

namespace monolitum\auth;

use monolitum\model\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Model;

class RoleModel
{
    private Model $model;
    private Attr $attrUserId;
    private Attr $attrEnabled;
    private Attr $attrType;

    public function __construct(readonly string $clazz, readonly string $userId, readonly string $enabled, readonly string $type){

    }

    function install(AuthManager $it): void
    {
        $this->model = EntitiesManager::findSelf()->getModel($this->clazz);
        $this->attrUserId = $this->model->getAttr($this->userId);
        $this->attrEnabled = $this->model->getAttr($this->enabled);
        $this->attrType = $this->model->getAttr($this->type);
    }

    /**
     * @return Model
     */
    public function getModel(): Model
    {
        return $this->model;
    }

    /**
     * @return Attr
     */
    public function getAttrUserId(): Attr
    {
        return $this->attrUserId;
    }

    /**
     * @return Attr
     */
    public function getAttrEnabled(): Attr
    {
        return $this->attrEnabled;
    }

    /**
     * @return Attr
     */
    public function getAttrType(): Attr
    {
        return $this->attrType;
    }

}
