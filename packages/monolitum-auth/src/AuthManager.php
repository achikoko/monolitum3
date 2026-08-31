<?php

namespace monolitum\auth;

use Closure;
use monolitum\auth\roles\RolesController;
use monolitum\auth\users\UsersController;
use monolitum\core\Find;
use monolitum\core\MNode;
use monolitum\core\security\CSRFTokenProvider;
use monolitum\model\Entity;

class AuthManager extends MNode implements CSRFTokenProvider
{

    private ?UsersController $usersController = null;
    private ?RolesController $rolesController = null;

    private mixed $sessionUser = null;
    private mixed $sessionRole = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param UsersController $usersController
     */
    public function setUsersController(UsersController $usersController): void
    {
        $this->usersController = $usersController;
        $this->usersController->install($this);
    }

    /**
     * @param RolesController $rolesController
     */
    public function setRolesController(RolesController $rolesController): void
    {
        $this->rolesController = $rolesController;
        $this->rolesController->install($this);
    }

//    public function addRoleModel(RoleModel $roleModel): void
//    {
//        $roleModel->install($this);
//        $this->roleModels[$roleModel->clazz] = $roleModel;
//    }
//
//    public function permission(string $permissionId, Closure $predicate): void
//    {
//        $this->permissions[$permissionId] = $predicate;
//    }
//
//    public function requireRole(string $roleClass): Entity {
//        $user = $this->requireLogin();
//
//        if(!isset($this->roleModels[$roleClass])){
//            throw new AuthPanic_NoPermissions();
//        }
//        /** @var RoleModel $roleModel */
//        $roleModel = $this->roleModels[$roleClass];
//
//        $role = Query::newQuery($roleModel->getModel())
//            ->filter([
//                $roleModel->getAttrUserId()->getId() => $user->getValue($this->userId),
//                $roleModel->getAttrEnabled()->getId() => true
//            ])
//            ->execute($this->managerDB)
//            ->firstAndClose();
//
//        if($role == null){
//            throw new AuthPanic_NoPermissions();
//        }
//
//        return $role;
//    }

    public function changePassword(Entity $user, string $plainPassword): bool
    {
        if($this->usersController == null){
            throw new AuthPanic("Login failed: UsersController is not set!");
        }

        return $this->usersController->changePassword($user, $plainPassword);

    }

    /**
     * @return void
     */
    private function sessionStartOrFail(): void
    {
        if (!session_id()){
            $result = session_start();
            if(!$result)
                throw new AuthPanic("Panic: Session not working!");
        }
    }

    public function logIn(string $username, string $password): bool
    {

        if($this->usersController == null){
            throw new AuthPanic("Login failed: UsersController is not set!");
        }

        $this->sessionUser = $this->usersController->loginByCredentials($username, $password);

        if($this->sessionUser != null){
            $this->sessionStartOrFail();

            $_SESSION['session_user'] = $this->usersController->getSessionUserString($this->sessionUser);

            $this->recoverSessionRole();

            return true;
        }

        return false;
    }

    public function switchRole(string $roleType): void
    {
        $this->requireLogin();

        if ($this->rolesController !== null) {
            $this->sessionRole = $this->rolesController->switchRoleByType($this->sessionUser, $roleType);

            if ($this->sessionRole != null) {
                $_SESSION['session_role'] = $this->rolesController->getSessionRoleString($this->sessionUser, $this->sessionRole);
            } else {
                $_SESSION['session_role'] = null;
            }
        }
    }

    private function requireLogin(): void
    {
        if($this->sessionUser == null){

            // Check if a session cookie exists (without starting the session)
            $sessionName = session_name(); // Default: 'PHPSESSID'

            if (!isset($_COOKIE[$sessionName])) {
                // No session cookie - no previous session exists
                // Send error page without calling session_start()
                throw new AuthPanic_NotLoggedIn();
            }

            $this->sessionStartOrFail();

            if(!isset($_SESSION['session_user']) || $_SESSION['session_user'] == null)
                throw new AuthPanic_NotLoggedIn();

            if($this->usersController == null){
                throw new AuthPanic("Login failed: UsersController is not set!");
            }

            $this->sessionUser = $this->usersController->recoverSessionUser($_SESSION['session_user']);

            if($this->sessionUser == null){
                $_SESSION['session_user'] = null;
                $_SESSION['session_role'] = null;
            }else{
                $this->recoverSessionRole();
            }

        }

        if($this->sessionUser != null){
            return;
        }

        throw new AuthPanic_NotLoggedIn();
    }

    /**
     * @return mixed
     */
    public function getSessionRole(): mixed
    {
        $this->requireLogin();
        return $this->sessionRole;
    }

    public function getSessionUser(): mixed
    {
        $this->requireLogin();
        return $this->sessionUser;
    }

    public function isCSRFSystemAvailable(): bool
    {
        // We can send the session cookie at any time, so we are available
        return true;
    }

    public function getCurrentCSRFToken(): string
    {

        $this->sessionStartOrFail();

        if(!isset($_SESSION['csrf_token']) || $_SESSION['csrf_token'] == null){
            $token = bin2hex(openssl_random_pseudo_bytes(32));
            if(!$token)
                throw new AuthPanic("Panic: RNG not working!");
            $_SESSION['csrf_token'] = $token;
        }

        return $_SESSION['csrf_token'];

    }

    public function requirePermission(string $permissionKey): void
    {
        if(!$this->hasPermission($permissionKey)){
            throw new AuthPanic_NoPermissions();
        }
    }

    public function hasPermissionInWhateverRole(string $permissionKey): bool
    {
        $this->requireLogin();

        $hasPermission = false;
        if($this->rolesController !== null){
            $hasPermission = $this->rolesController->hasPermissionInWhateverRole($this->sessionUser, $permissionKey);
        }

        if(!$hasPermission){
            if($this->usersController != null){
                $hasPermission = $this->usersController->hasPermission($this->sessionUser, $permissionKey);
            }
        }

        return $hasPermission;
    }

    public function hasPermission(string $permissionKey): bool
    {
        $this->requireLogin();

        $hasPermission = false;
        if($this->rolesController !== null){
            $hasPermission = $this->rolesController->hasPermission($this->sessionUser, $this->sessionRole, $permissionKey);
        }

        if(!$hasPermission){
            if($this->usersController != null){
                $hasPermission = $this->usersController->hasPermission($this->sessionUser, $permissionKey);
            }
        }

        return $hasPermission;
    }

    private function logout(): void
    {

        // 1. Start the session (if not already started)
        session_start();

        // 2. Clear all session variables
        $_SESSION = [];

        // 3. Destroy the session
        session_destroy();

        // 4. (Optional) Delete the session cookie
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        $this->sessionUser = null;
        $this->sessionRole = null;

    }

    public function isLoggedIn(): bool
    {

        // Check if a session cookie exists (without starting the session)
        $sessionName = session_name(); // Default: 'PHPSESSID'

        if (!isset($_COOKIE[$sessionName])) {
            // No session cookie - no previous session exists
            // Send error page without calling session_start()
            return false;
        }

        $this->sessionStartOrFail();

        if(!isset($_SESSION['session_user']) || $_SESSION['session_user'] == null)
            return false;

        return true;
    }

    public static function pushRequireLogin(): void
    {
        /** @var AuthManager $manager */
        $manager = Find::pushAndGet(AuthManager::class);
        $manager->requireLogin();
    }

    /**
     * @param string $permissionId
     * @return void
     */
    public static function pushRequirePermission(string $permissionId): void
    {
        /** @var AuthManager $manager */
        $manager = Find::pushAndGet(AuthManager::class);
        $manager->requirePermission($permissionId);
    }

    /**
     * @param string $permissionId
     * @return bool
     */
    public static function pushHasPermission(string $permissionId): bool
    {
        /** @var AuthManager $manager */
        $manager = Find::pushAndGet(AuthManager::class);
        return $manager->hasPermission($permissionId);
    }

    public static function pushLogout(): void
    {
        /** @var AuthManager $manager */
        $manager = Find::pushAndGet(AuthManager::class);
        $manager->logout();
    }

    public static function pushIsLoggedIn(): bool
    {
        /** @var AuthManager $manager */
        $manager = Find::pushAndGet(AuthManager::class);
        return $manager->isLoggedIn();
    }

    /**
     * @return void
     */
    private function recoverSessionRole(): void
    {
        if ($this->rolesController !== null) {
            $this->sessionRole = $this->rolesController->recoverSessionRole($this->sessionUser, $_SESSION['session_role'] ?? null);

            if ($this->sessionRole != null) {
                $_SESSION['session_role'] = $this->rolesController->getSessionRoleString($this->sessionUser, $this->sessionRole);
            } else {
                $_SESSION['session_role'] = null;
            }
        }
    }

    /**
     * @return UsersController|null
     */
    public function getUsersController(): ?UsersController
    {
        return $this->usersController;
    }

}
