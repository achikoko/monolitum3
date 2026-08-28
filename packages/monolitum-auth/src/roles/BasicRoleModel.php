<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;
use monolitum\auth\users\PermissionClosure;
use monolitum\model\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;

class BasicRoleModel
{

    private AuthManager $manager;

    private Model $model;

    private Attr $attrRoleId;

    private array $permissions = [];
    private array $attrsPermissions = [];

    public function __construct(
        readonly string $clazz,
        readonly string $roleId,
    ){

    }

    public function addBooleanPermission(string $permission): self
    {
        $this->permissions[] = $permission;
        return $this;
    }

    public function addPermissionClosure(RolePermissionClosure $permission): self
    {
        $this->permissions[] = $permission;
        return $this;
    }

    function install(AuthManager $manager): void
    {
        $this->manager = $manager;
        $this->model = EntitiesManager::findSelf()->getModel($this->clazz);
        $this->attrRoleId = $this->model->getAttr($this->roleId);

        foreach ($this->permissions as $permission) {
            if($permission instanceof PermissionClosure) {
                $this->attrsPermissions[$permission->permissionKey] = $permission;
            }else{
                $this->attrsPermissions[$permission] = $this->model->getAttr($permission);
            }
        }
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
    public function getAttrRoleId(): Attr
    {
        return $this->attrRoleId;
    }

    public function hasPermission(Entity $user, Entity $globalRole, Entity $specificRole, string $permissionKey): bool
    {

        if(array_key_exists($permissionKey, $this->attrsPermissions)){

            $attr = $this->attrsPermissions[$permissionKey];

            if($attr instanceof RolePermissionClosure){
                $result = call_user_func($attr->closure, $this->manager, $user, $globalRole, $specificRole);
                return $result === true;
            }else if ($attr instanceof Attr){
                return $specificRole->getBool($attr) === true;
            }

        }

        return false;
    }

}
