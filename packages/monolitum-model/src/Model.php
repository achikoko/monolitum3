<?php
namespace monolitum\model;

use monolitum\core\Find;

class Model extends AnonymousModel
{

    /**
     * @param class-string $instanceableEntityClass
     * @param string|null $id
     */
    public function __construct(public readonly string $instanceableEntityClass, public readonly ?string $id = null)
    {

    }

    /**
     * @return string
     */
    public function getIdOrClass(): string
    {
        return $this->id ?: $this->instanceableEntityClass;
    }

    /**
     * @return class-string
     */
    public function getInstanceableClass(): string
    {
        return $this->instanceableEntityClass;
    }

    public function __toString()
    {
        if(!is_null($this->id))
            return $this->id;
        return  parent::__toString();
    }

    public static function pushFindByName(string|Model $class): Model
    {
        if ($class instanceof Model)
            return $class;
        return EntitiesManager::findSelf()->getModel($class);
    }

    public static function pushInstance(Model|string $class, $forInsert = false): Entity
    {
        /** @var EntitiesManager $entities */
        $entities = Find::push(EntitiesManager::class)->getResponse();
        return $entities->instance($class, $forInsert);
    }

}

