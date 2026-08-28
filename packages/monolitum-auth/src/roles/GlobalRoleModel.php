<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;
use monolitum\model\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Model;

class GlobalRoleModel
{

    private AuthManager $manager;

    private Model $model;

    private Attr $attrId;
    private Attr $attrUserId;
    private Attr $attrEnabled;
    private Attr $attrDefault;
    private Attr $attrRoleType;

    private array $permissions = [];
    private array $attrsPermissions = [];

    public function __construct(
        readonly string $clazz,
        readonly string $id,
        readonly string $userId,
        readonly string $enabled,
        readonly string $default,
        readonly string $roleType
    ){

    }

    function install(AuthManager $manager): void
    {
        $this->manager = $manager;
        $this->model = EntitiesManager::findSelf()->getModel($this->clazz);
        $this->attrId = $this->model->getAttr($this->id);
        $this->attrUserId = $this->model->getAttr($this->userId);
        $this->attrEnabled = $this->model->getAttr($this->enabled);
        $this->attrDefault = $this->model->getAttr($this->default);
        $this->attrRoleType = $this->model->getAttr($this->roleType);

//        foreach ($this->permissions as $permission) {
//            if($permission instanceof PermissionClosure) {
//                $this->attrsPermissions[$permission->permissionKey] = $permission;
//            }else{
//                $this->attrsPermissions[$permission] = $this->model->getAttr($permission);
//            }
//        }
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
    public function getAttrId(): Attr
    {
        return $this->attrId;
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
    public function getAttrDefault(): Attr
    {
        return $this->attrDefault;
    }

    /**
     * @return Attr
     */
    public function getAttrRoleType(): Attr
    {
        return $this->attrRoleType;
    }

//    /**
//     * @return array
//     */
//    public function getAttrsPermissions(): array
//    {
//        return $this->attrsPermissions;
//    }
//
//    public function hasPermission(Entity $user, string $permissionKey): bool
//    {
//
//        if(array_key_exists($permissionKey, $this->attrsPermissions)){
//
//            $callable = $this->attrsPermissions[$permissionKey];
//
//            if($callable instanceof PermissionClosure){
//                $result = call_user_func($callable->closure, $this->manager, $user);
//                return $result === true;
//            }else if ($callable instanceof Attr){
//                return $user->getBool($callable) === true;
//            }
//
//        }
//
//        return false;
//    }

}
