<?php

namespace monolitum\auth\users;

use monolitum\auth\AuthManager;
use monolitum\auth\Session;
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

    public function loginByCredentials(string $username, string $password): ?Session
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

            return new Session($user);
        }

    }

    public function getSessionString(Session $session): string
    {
        return strval($session->user->getInt($this->usersModel->getAttrUserId()));
    }

    public function recoverSession(mixed $session_string): ?Session
    {

        $userIterable = Query::newQuery($this->usersModel->getModel())
            ->filter([
                $this->usersModel->getAttrUserId()->getId() => intval($session_string)
            ])
            ->store()
            ->execute();

        /** @var Entity|null $user */
        $user = $userIterable->firstAndClose();

        if($user == null)
            return null;

        return new Session($user);
    }

    public function hasPermission(Session $session, string $permissionKey): bool
    {
        return $this->usersModel->hasPermission($session->user, $permissionKey);
    }

    public function changePassword(Entity $user, string $plainPassword): bool
    {
        $user->setValue($this->usersModel->getAttrPassword(), password_hash(
            $plainPassword,
            PASSWORD_DEFAULT,
            array('cost' => 9)
        ));
        return true;
    }

}
