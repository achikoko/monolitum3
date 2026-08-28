<?php

namespace monolitum\auth\users;

use monolitum\auth\AuthManager;
use monolitum\database\Query;
use monolitum\model\Entity;

class SimpleUsersController implements UsersController
{

    public function __construct(
        public readonly UsersModel $usersModel,
        public readonly array $permissions,
    ){

    }

    public function install(AuthManager $manager): void
    {
        $this->usersModel->install($manager);
    }

    public function loginByCredentials(string $username, string $password): ?Entity
    {

        $userIterable = Query::newQuery($this->usersModel->getModel())
            ->filter([
                $this->usersModel->getAttrUsername()->getId() => $username,
                $this->usersModel->getAttrEnabled()->getId() => true
            ])
            ->store()
            ->execute();

        /** @var Entity|null $user */
        $user = $userIterable->firstAndClose();

        if($user === null){
            return null;
        }else{

            $userPassword = $user->getString($this->usersModel->getAttrPassword());

            if($userPassword === null)
                return null;

            if(!password_verify($password, $userPassword))
                return null;

            return $user;
        }

    }

    public function getSessionUserString(mixed $sessionUser): string
    {
        /** @var Entity $sessionUser */
        return strval($sessionUser->getInt($this->usersModel->getAttrUserId()));
    }

    public function recoverSessionUser(mixed $sessionUserString): ?Entity
    {

        $userIterable = Query::newQuery($this->usersModel->getModel())
            ->filter([
                $this->usersModel->getAttrUserId()->getId() => intval($sessionUserString)
            ])
            ->store()
            ->execute();

        /** @var Entity|null $user */
        $user = $userIterable->firstAndClose();

        if($user == null)
            return null;

        return $user;
    }

    public function hasPermission(mixed $sessionUser, string $permissionKey): bool
    {
        /** @var Entity $sessionUser */
        return $this->usersModel->hasPermission($sessionUser, $permissionKey);
    }

    public function changePassword(mixed $sessionUser, string $plainPassword): bool
    {
        $sessionUser->setValue($this->usersModel->getAttrPassword(), password_hash(
            $plainPassword,
            PASSWORD_DEFAULT,
            array('cost' => 9)
        ));
        return true;
    }

    public function getUserId(mixed $sessionUser): ?int
    {
        /** @var Entity $sessionUser */
        return $sessionUser->getInt($this->usersModel->getAttrUserId());
    }

}
