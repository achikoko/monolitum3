<?php

namespace monolitum\auth\users;

use monolitum\auth\AuthManager;
use monolitum\model\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;

class UsersModel
{

    private AuthManager $manager;

    private Model $model;

    private Attr $attrUserId;
    private Attr $attrUsername;
    private Attr $attrPassword;
    private Attr $attrEnabled;

    private array $permissions = [];
    private array $attrsPermissions = [];

    public function __construct(
        readonly string $clazz,
        readonly string $userId,
        readonly string $username,
        readonly string $password,
        readonly string $enabled
    ){

    }

    public function addBooleanPermission(string $permission): self
    {
        $this->permissions[] = $permission;
        return $this;
    }

    public function addPermissionClosure(PermissionClosure $permission): self
    {
        $this->permissions[] = $permission;
        return $this;
    }

    function install(AuthManager $manager): void
    {
        $this->manager = $manager;
        $this->model = EntitiesManager::findSelf()->getModel($this->clazz);
        $this->attrUserId = $this->model->getAttr($this->userId);
        $this->attrUsername = $this->model->getAttr($this->username);
        $this->attrPassword = $this->model->getAttr($this->password);
        $this->attrEnabled = $this->model->getAttr($this->enabled);

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
    public function getAttrUserId(): Attr
    {
        return $this->attrUserId;
    }

    /**
     * @return Attr
     */
    public function getAttrUsername(): Attr
    {
        return $this->attrUsername;
    }

    /**
     * @return Attr
     */
    public function getAttrPassword(): Attr
    {
        return $this->attrPassword;
    }

    /**
     * @return Attr
     */
    public function getAttrEnabled(): Attr
    {
        return $this->attrEnabled;
    }

    /**
     * @return array
     */
    public function getAttrsPermissions(): array
    {
        return $this->attrsPermissions;
    }

    public function hasPermission(Entity $user, string $permissionKey): bool
    {

        if(array_key_exists($permissionKey, $this->attrsPermissions)){

            $callable = $this->attrsPermissions[$permissionKey];

            if($callable instanceof PermissionClosure){
                $result = call_user_func($callable->closure, $this->manager, $user);
                return $result === true;
            }else if ($callable instanceof Attr){
                return $user->getBool($callable) === true;
            }

        }

        return false;
    }

}
