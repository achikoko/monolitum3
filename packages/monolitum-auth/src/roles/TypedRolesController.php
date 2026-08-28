<?php

namespace monolitum\auth\roles;

use monolitum\auth\AuthManager;
use monolitum\auth\users\UsersController;
use monolitum\core\panic\DevPanic;
use monolitum\core\panic\UserPanic;
use monolitum\database\Query;

class TypedRolesController implements RolesController
{

    /** @var array<string, RoleController>  */
    public array $roleControllers;

    private ?UsersController $usersController;

    public function __construct(public readonly GlobalRoleModel $globalRoleModel){

    }

    public function addRoleController(RoleController $roleController): self{
        $this->roleControllers[$roleController->getType()] = $roleController;
        return $this;
    }

    public function install(AuthManager $manager): void
    {
        $this->usersController = $manager->getUsersController();
        if($this->usersController === null){
            throw new DevPanic("TypedRolesController requires a UsersController");
        }

        $this->globalRoleModel->install($manager);
        foreach ($this->roleControllers as $roleController){
            $roleController->install($manager);
        }
    }

    public function hasPermissionInWhateverRole(mixed $sessionUser, string $permissionKey): bool
    {
        // TODO: Implement hasPermissionInWhateverRole() method.
        throw new DevPanic("Unimplemented");
    }

    public function hasPermission(mixed $sessionUser, mixed $sessionRole, string $permissionKey): bool
    {
        /** @var RolePair $sessionRole */
        $roleType = $sessionRole->globalRole->getString($this->globalRoleModel->getAttrRoleType());
        $roleController = $this->roleControllers[$roleType];
        if($roleController === null){
            throw new DevPanic("RoleController not found.");
        }

        return $roleController->hasPermission($sessionUser, $sessionRole, $permissionKey);

    }

    public function recoverSessionRole(mixed $sessionUser, ?string $sessionRoleString): ?RolePair
    {
        $query = Query::newQuery($this->globalRoleModel->getModel())
            ->sort($this->globalRoleModel->getAttrDefault(), true)
            ->limit(1);

        if($sessionRoleString === null){
            $query->filter([
                $this->globalRoleModel->getAttrUserId()->getId() => $this->usersController->getUserId($sessionUser),
                $this->globalRoleModel->getAttrEnabled()->getId() => true,
            ]);
        }else{
            $query->filter([
                $this->globalRoleModel->getAttrId()->getId() => intval($sessionRoleString),
                $this->globalRoleModel->getAttrUserId()->getId() => $this->usersController->getUserId($sessionUser),
                $this->globalRoleModel->getAttrEnabled()->getId() => true,
            ]);
        }


        foreach ($this->roleControllers as $roleControllerType => $roleController){
            $query->outerJoin($this->globalRoleModel->getAttrId()->getId(), Query::newQueryJoin($roleController->getModelClass(), $roleController->getAttrRoleId())
                ->select()
                ->limit(1)
            );
        }

        $globalRole = $query->execute()->firstAndClose();

        if($globalRole === null){
            return null;
        }

        $roleType = $globalRole->getString($this->globalRoleModel->getAttrRoleType());
        $roleController = $this->roleControllers[$roleType];
        if($roleController === null){
            throw new DevPanic("RoleController not found.");
        }

        $specificRole = $globalRole->getJoinedSingleEntity($roleController->getModelClass());

        if($specificRole === null){
            throw new UserPanic("Failed due to a global role that have not a specific part.");
        }

        return new RolePair($globalRole, $specificRole);

    }

    public function getSessionRoleString(mixed $sessionUser, mixed $sessionRole): ?string
    {
        /** @var ?RolePair $sessionRole */
        return $sessionRole === null ? null : strval($sessionRole->globalRole->getInt($this->globalRoleModel->getAttrId()));
    }

    public function switchRoleByType(mixed $sessionUser, string $roleType): ?RolePair
    {
        $roleController = $this->roleControllers[$roleType];
        if($roleController === null){
            throw new DevPanic("RoleController not found.");
        }

        $globalRole = Query::newQuery($this->globalRoleModel->getModel())
            ->filter([
                $this->globalRoleModel->getAttrUserId()->getId() => $this->usersController->getUserId($sessionUser),
                $this->globalRoleModel->getAttrEnabled()->getId() => true,
                $this->globalRoleModel->getAttrRoleType()->getId() => $roleType,
            ])
            ->sort($this->globalRoleModel->getAttrDefault(), true)
            ->join($this->globalRoleModel->getAttrId(), Query::newQueryJoin($roleController->getType(), $roleController->getAttrRoleId())
                ->select()
                ->limit(1)
            )
            ->limit(1)
            ->execute()
            ->firstAndClose();

        if($globalRole === null){
            return null;
        }

        $specificRole = $globalRole->getJoinedSingleEntity($roleController->getModelClass());

        if($specificRole === null){
            throw new UserPanic("Failed due to a global role that have not a specific part.");
        }

        return new RolePair($globalRole, $specificRole);
    }

}
