<?php

namespace monolitum\model;

use Closure;
use monolitum\core\Find;
use monolitum\core\MNode;
use monolitum\core\panic\DevPanic;

class EntitiesManager extends MNode implements EntityPersister
{

    /**
     * @var array<class-string, Model>
     */
    private array $models = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param class-string|Model $class
     */
    public function getModel(Model|string $class): Model {
        assert($class != null);

        if($class instanceof Model)
            return $class;

        if(array_key_exists($class, $this->models))
            return $this->models[$class];

        /** @var Entity $entity */
        $entity = new $class();
        if(!($entity instanceof Entity)){
            throw new DevPanic("Expected " . $class . " to be an instance of Entity.");
        }
        $model = $entity->buildModel();
        $this->models[$class] = $model;
        return $model;

    }

    /**
     * @param string|Model $entityModel
     * @param bool $forInsert
     * @return Entity
     */
    public function instance(Model|string $entityModel, bool $forInsert = false): Entity
    {
        $model = $this->getModel($entityModel);
        $class = $model->getInstanceableClass();
        /** @var Entity $inst */
        $inst = new $class();
        $inst->_setModel($model);
        if($forInsert)
            $inst->_setManager($this);
        return $inst;
    }

    /**
     * @param Entity $entity
     * @return void
     */
    public function _notifyEntityChanged(Entity $entity): void
    {
        /** @var EntityPersister $face */
        $face = Find::push(EntityPersister::class)->getResponse();
        $face->_notifyEntityChanged($entity);
    }

    public function _executeInsertEntity(Entity $entity): array
    {
        /** @var EntityPersister $face */
        $face = Find::push(EntityPersister::class)->getResponse();
        return $face->_executeInsertEntity($entity);
    }

    public function _executeUpdateEntity(Entity $entity): array
    {
        /** @var EntityPersister $face */
        $face = Find::push(EntityPersister::class)->getResponse();
        return $face->_executeUpdateEntity($entity);
    }

    public function _executeDeleteEntity(Entity $entity): int
    {
        /** @var EntityPersister $face */
        $face = Find::push(EntityPersister::class)->getResponse();
        return $face->_executeDeleteEntity($entity);
    }

}
