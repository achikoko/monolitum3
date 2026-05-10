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

        if($class instanceof AnonymousModel)
            return $class;

        if(array_key_exists($class, $this->models))
            return $this->models[$class];

        /** @var Entity $entity */
        $entity = new $class();
        if(!($entity instanceof Entity)){
            throw new DevPanic("Expected " . $class . " to be an instance of Entity.");
        }
        $model = $entity->buildModel($this);
        $this->models[$class] = $model;
        return $model;

    }

    /**
     * Instances a new entity for the provided model.
     * @param string|Model $entityModel Model of the entity
     * @param bool $forInsert Flag to tell the entity to store database changes then to be applyed.
     * @param Entity|null $cloneOf Optional entity to copy attributes from.
     * @return Entity
     */
    public function instance(Model|string $entityModel, bool $forInsert = false, ?Entity $cloneOf = null): Entity
    {
        $model = $this->getModel($entityModel);
        $class = $model->getInstanceableClass();

        if($cloneOf !== null){
            if($cloneOf->getModel() !== $model){
                throw new DevPanic("Only identical model is allowed for cloning an Entity");
            }
        }

        /** @var Entity $inst */
        $inst = new $class();
        $inst->_setModel($model);
        if($forInsert)
            $inst->_setManager($this);

        if($cloneOf !== null){
            foreach($model->getAttrs() as $attr){
                $inst->setValue($attr, $cloneOf->getValue($attr));
            }
        }

        return $inst;
    }

    /**
     * Instances a new entity and "parses" the data array (overwrites cloned attributes from `$cloneOf` param).
     *
     * Values in the data array are passed through Attr->validate(...) to get the actual value. Just if `ValidatedValue->isWellFormat()`
     * returns `true`, the value is included in the entity.
     * @param Model|string $entityModel
     * @param array $data
     * @param bool $forInsert
     * @param Entity|null $cloneOf
     * @return Entity
     */
    public function instanceWithData(Model|string $entityModel, array $data, bool $forInsert = false, ?Entity $cloneOf = null): Entity
    {
        $model = $this->getModel($entityModel);

        $inst = $this->instance($entityModel, $forInsert, $cloneOf);

        foreach($model->getAttrs() as $attr){
            if(array_key_exists($attr->getId(), $data)){
                $validated = $attr->validate($data[$attr->getId()]);
                if($validated->isWellFormat()){
                    $inst->setValue($attr, $validated->getValue());
                }
            }
        }

        return $inst;
    }

    public function extendModel(Model|string $baseModel, string $instanceableEntityClass, ?string $id = null): Model
    {
        return $this->getModel($baseModel)->clone($instanceableEntityClass, $id);
    }

    public function extendModelToAnonymous(Model|string $baseModel): AnonymousModel
    {
        return $this->getModel($baseModel)->cloneAnonymous();
    }

    public function writeToArray(Entity $entity, ?array $filterAttrs = null): array
    {
        $array = [];
        foreach ($entity->getModel()->getAttrs() as $attr){
            if($filterAttrs === null || in_array($attr->getId(), $filterAttrs)) {
                $value = $entity->getValue($attr);
                if ($value !== null) {
                    $array[$attr->getId()] = $attr->stringValue($value);
                }
            }
        }
        return $array;
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
