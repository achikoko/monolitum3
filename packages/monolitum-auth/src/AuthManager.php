<?php

namespace monolitum\auth;

use Closure;
use monolitum\core\Find;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;
use monolitum\core\security\CSRFTokenProvider;
use monolitum\database\DatabaseManager;
use monolitum\database\Query;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;

class AuthManager extends MNode implements CSRFTokenProvider
{

    private string $entityClass;

    private string $usernameAttr;
    private string $passwordAttr;
    private string $enabledAttr;

    private EntitiesManager $entitiesManager;

    private Model $entityModel;

    /**
     * @var array<string, Closure>
     */
    private array $permissions = [];

    /**
     * Logged user set by requireLogin
     * @var Entity
     */
    private ?Entity $user = null;

    private DatabaseManager $managerDB;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setUserModel(string $entityClass, string $usernameAttr, string $passwordAttr, string $enabledAttr): void
    {
        $this->entityClass = $entityClass;
        $this->usernameAttr = $usernameAttr;
        $this->passwordAttr = $passwordAttr;
        $this->enabledAttr = $enabledAttr;

        $this->entityModel = $this->entitiesManager->getModel($this->entityClass);

    }

    public function permission(string $permissionId, Closure $predicate): void
    {
        $this->permissions[$permissionId] = $predicate;
    }

    public function changePassword(Entity $user, string $plainPassword): self
    {
        // TODO hash
        $user->setValue($this->passwordAttr, password_hash(
            $plainPassword,
            PASSWORD_DEFAULT,
            array('cost' => 9)
        ));
        return $this;
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

    protected function onBuild(): void
    {
        $this->entitiesManager = Find::pushAndGet(EntitiesManager::class);
        $this->managerDB = Find::pushAndGet(DatabaseManager::class);

        parent::onBuild();
    }

    public function logIn(string $username, string $password): bool
    {

        $userIterable = Query::newQuery($this->entityModel)
            ->filter([
                $this->usernameAttr => $username,
                $this->enabledAttr => true
            ])
            ->store()
            ->execute($this->managerDB);

        /** @var Entity|null $user */
        $this->user = $userIterable->firstAndClose();

        if($this->user === null){
            return false;
        }else{

            $userPassword = $this->user->getString($this->passwordAttr);

            if($userPassword === null)
                return false;

            if(!password_verify($password, $userPassword))
                return false;

            $this->sessionStartOrFail();

            $_SESSION['username'] = $this->user->getString($this->usernameAttr);

            return true;
        }

    }

    private function requireLogin(): void
    {
        if($this->user == null){

            if(session_id() === null)
                throw new AuthPanic_NoUser();

            session_start();

            if(!isset($_SESSION['username']) || $_SESSION['username'] == null)
                throw new AuthPanic_NoUser();

            $userIterable = Query::newQuery($this->entityModel)
                ->filter([
                    $this->usernameAttr => $_SESSION['username']
                ])
                ->store()
                ->execute($this->managerDB);

            /** @var Entity|null $user */
            $this->user = $userIterable->firstAndClose();

            if($this->user == null)
                $_SESSION['username'] = null;
        }

    }

    private function getUser()
    {
        if($this->user == null){

            $this->sessionStartOrFail();

            if(!isset($_SESSION['username']) || $_SESSION['username'] == null)
                return null;

            $userIterable = Query::newQuery($this->entityModel)
                ->filter([
                    $this->usernameAttr => $_SESSION['username']
                ])
                ->store()
                ->execute($this->managerDB);

            /** @var Entity|null $user */
            $this->user = $userIterable->firstAndClose();

            // TODO when user have a lifetime, update the cookie
//            setcookie(session_name(),session_id(),time()+$lifetime);

        }

        return $this->user;
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

    public function requirePermission(string $permissionId): void
    {
        $this->requireLogin();
        $user = $this->getUser();

        if(array_key_exists($permissionId, $this->permissions)){

            $callable = $this->permissions[$permissionId];

            if(!$callable($user))
                throw new AuthPanic_NoPermissions();

        }else{
            throw new DevPanic("Permission '" . $permissionId . "' is not defined.");
        }

    }

    public function hasPermission(string $permissionId): bool
    {
        $this->requireLogin();
        $user = $this->getUser();

        if(array_key_exists($permissionId, $this->permissions)){

            $callable = $this->permissions[$permissionId];

            if(!$callable($user))
                return false;

        }else{
            return false;
        }

        return true;
    }

    private function logout(): void
    {
        if(session_id()){
            session_destroy();
        }
//            session_start();
//        $_SESSION['username'] = null;

    }

    private function isLoggedIn(): bool
    {
        if(! session_id())
            return false;

        if(!isset($_SESSION['username']) || $_SESSION['username'] == null)
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

}
