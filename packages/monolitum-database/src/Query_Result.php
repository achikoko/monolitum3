<?php

namespace monolitum\database;

use Iterator;
use monolitum\core\Find;
use monolitum\core\util\MClosableIterator;
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_Bool;
use monolitum\model\attr\Attr_Date;
use monolitum\model\attr\Attr_DateTime;
use monolitum\model\attr\Attr_Decimal;
use monolitum\model\attr\Attr_Int;
use monolitum\model\attr\Attr_String;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;
use PDO;
use PDOStatement;

class Query_Result implements MClosableIterator, Iterator
{

    private EntitiesManager $entityManager;

    private ?Entity $currentEntity = null;

    private array|false $nextRow = false;

    private ?int $iteratorKey = null;

    private bool $initialized = false;
    private bool $lastEntityWasParsed = false;
    private bool $finished = false;

    /**
     * @param DatabaseManager $manager
     * @param Model $model
     * @param Query_Entities_Executor $select
     * @param bool $protectForUpdate
     * @param PDOStatement $stmt
     */
    public function __construct(
        DatabaseManager $manager,
        private readonly Model $model,
        private readonly Query_Entities_Executor $select,
        private readonly bool $protectForUpdate,
        private readonly PDOStatement $stmt)
    {

        $this->entityManager = Find::pushAndGetFrom(EntitiesManager::class, $manager);
    }

    public function hasNext(): bool
    {
        if($this->finished)
            return false;
        if($this->currentEntity != null)
            return true;
        $this->next(); // Consume this one
        return $this->currentEntity !== null;
    }

    /**
     * @return Entity|null
     */
    public function nextConsume(): ?Entity
    {
        if($this->finished)
            return null;

        if($this->currentEntity != null){
            $ret = $this->currentEntity;
            $this->next(); // Consume this one
            return $ret;
        }else{
            $this->next(); // Consume this one
            return $this->currentEntity;
        }

    }

    public function next(): void
    {
        if($this->lastEntityWasParsed)
            $this->finished = true;

        if($this->finished)
            return;

        if(!$this->initialized){
            $this->initialized = true;

            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if($row === false){
                $this->close();
                $this->finished = true;
                return;
            }
            $this->nextRow = $row;

        }

        $joinIdsStack = [];

        $idsMandatory = $this->protectForUpdate || $this->select->hasJoins();

        $entity = null;
        while ($this->nextRow !== false){
            $tableIndex = 0;
            $parsedEntity = $this->parseEntity(
                $this->select,
                $this->model,
                $idsMandatory,
                null,
                $tableIndex,
                $joinIdsStack
            );

            if($parsedEntity === null){
                // Encountered the next root entity, break without advancing the cursor so next iteration can be read
                break;
            }

            if($entity === null){
                $entity = $parsedEntity;
                if(!$idsMandatory){
                    // Not interested in iterate more for a simple query
                    // Tho, advance the cursor anyways

                    $this->nextRow = $this->stmt->fetch(PDO::FETCH_ASSOC);
                    if($this->nextRow === false){
                        $this->close();
                    }

                    break;
                }
            }else{
                assert($entity === $parsedEntity);
            }

            $this->nextRow = $this->stmt->fetch(PDO::FETCH_ASSOC);
            if($this->nextRow === false){
                $this->close();
            }

        }

        assert($entity !== null);

        $this->limitJoins($this->select, $entity);

        if(is_null($this->iteratorKey)) {
            $this->iteratorKey = 0;
        } else {
            $this->iteratorKey++;
        }

        $this->currentEntity = $entity;

    }

    public function firstAndClose(): ?Entity
    {
        $entity = $this->nextConsume();
        $this->close();
        return $entity;
    }

    /**
     *
     */
    public function close(): void
    {
        $this->lastEntityWasParsed = true;
        $this->stmt->closeCursor();
    }

    #[\ReturnTypeWillChange]
    public function current(): ?Entity
    {
        return $this->currentEntity;
    }

    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->iteratorKey;
    }

    public function valid(): bool
    {
        return !$this->finished;
    }

    public function rewind(): void
    {
        if(!$this->initialized){
            $this->next();
        }
        // Ignored
    }

    private function parseEntity(Query_Entities $query, Model $model, bool $idsMandatory, ?Entity $parentEntity, int &$tableIndex, array &$joinIdsStack): ?Entity
    {
        $selectedAttrsIds = $query->getSelectAttrs();
        $tableAlias = DatabaseManager::_computeTableAlias($tableIndex);

        /** @var ?array $keys */
        if($idsMandatory) {

            assert($tableIndex <= sizeof($joinIdsStack));

            if($tableIndex == sizeof($joinIdsStack)){
                $joinIdsStack[] = null;
            }

            $keys = null;
            foreach ($model->getAttrs() as $attr) {
                /** @var AttrExt_DB $ext */
                $ext = $attr->findExtension(AttrExt_DB::class);
                if ($ext !== null && $ext->isPrimaryKey()) {
                    $val = $this->parseValue($tableAlias, $attr, null);

                    if ($val === null) {
                        break; // No entity
                    }

                    if ($keys === null) {
                        $keys = [$val];
                    } else {
                        $keys[] = $val;
                    }

                }
            }

            if($keys !== null){
                $entity = $joinIdsStack[$tableIndex];
                if($entity != null){
                    // Check if it is a new entity
                    if($this->compareIds($model, $entity, $keys)){
                        // Same entity
                        // Skip reading attributes and go to joins
                        $read = false;
                    }else{
                        // New Entity different that reading one
                        if($parentEntity == null){
                            // Root entity, don't advance more!!
                            return null;
                        }else{
                            // Read entity
                            $entity = $this->entityManager->instance($model);
                            $this->assignIds($model, $entity, $keys);
                            $read = true;
                            $joinIdsStack[$tableIndex] = $entity;
                        }
                    }
                }else{
                    // New entity
                    $entity = $this->entityManager->instance($model);
                    $this->assignIds($model, $entity, $keys);
                    $read = true;
                    $joinIdsStack[$tableIndex] = $entity;
                }

            }else{
                // No entity, skip reading, cannot be joined
                return null;
            }


        }else{
            $entity = $this->entityManager->instance($model);
            $read = true;
        }

        if($read){

            foreach ($model->getAttrs() as $attr) {
                /** @var AttrExt_DB $ext */
                $ext = $attr->findExtension(AttrExt_DB::class);
                if (
                    // Not an ID
                    (!$idsMandatory || $ext === null || !$ext->isPrimaryKey())
                    && (
                        // Selected
                        $selectedAttrsIds === true
                        || is_array($selectedAttrsIds) && in_array($attr->getId(), $selectedAttrsIds)
                    )
                ) {
                    $this->parseValue($tableAlias, $attr, $entity);
                }
            }

            if($this->protectForUpdate){
                $entity->_setManager($this->entityManager);
            }else{
                $entity->_protectWrite();
            }

        }

        $joinIndex = 0;
        foreach ($query->getJoins() as $joinTuple) {
            $join = $joinTuple->join;
            $joinModel = $this->entityManager->getModel($join->model);
            $tableIndex++;

            $childEntity = $this->parseEntity($join, $joinModel, true, $entity, $tableIndex, $joinIdsStack);
            if($childEntity !== null){
                $entity->_addJointEntity($joinIndex, $childEntity);
            }

            $joinIndex++;
        }

        // If parent entity is root, never return null here, it is returned null only when a new root entity has been found
        // and the 'return' statement that does that is above here
        return $read || $parentEntity === null ? $entity : null;
    }

    private function parseValue(string $tableAlias, Attr $attr, ?Entity $entity): mixed
    {
        $columnName = DatabaseManager::_computeSelectAttrAlias($tableAlias, $attr);

        $rowValue = $this->nextRow[$columnName];

        if($rowValue !== null){

            if($attr instanceof Attr_String){
                $val = strval($rowValue);
                $entity?->setString($attr, $val);
                return $val;
            }else if($attr instanceof Attr_Int){
                $val = intval($rowValue);
                $entity?->setInt($attr, $val);
                return $val;
            }else if($attr instanceof Attr_Decimal){
                $val = intval($rowValue);
                $entity?->setInt($attr, $val);
                return $val;
            }else if($attr instanceof Attr_Bool){
                if(is_int($rowValue))
                    $rowValue = $rowValue != 0;
                else if($rowValue === "true")
                    $rowValue = true;
                else if($rowValue === "false")
                    $rowValue = false;
                $entity?->setBool($attr, $rowValue);
                return $rowValue;
            }else if($attr instanceof Attr_Date || $attr instanceof Attr_DateTime){
                $val = date_create($rowValue);
                $entity?->setDate($attr, $val);
                return $val;
            }else if($attr instanceof DatabaseableAttr){
                $val = $attr->parseValue($rowValue);
                $entity?->setValue($attr, $val);
                return $val;
            }

        }

        return null;

    }

    private function compareIds(Model $model, Entity $lastEntity, array $keys): bool
    {
        $idx = 0;
        foreach ($model->getAttrs() as $attr) {
            /** @var AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if ($ext !== null && $ext->isPrimaryKey()) {
                $currentVal = $lastEntity->getValue($attr);
                $readVal = $keys[$idx++];

                if($currentVal !== $readVal){
                    return false;
                }

            }
        }

        return true;
    }

    private function assignIds(Model $model, Entity $entity, array $keys): void
    {
        $idx = 0;
        foreach ($model->getAttrs() as $attr) {
            /** @var AttrExt_DB $ext */
            $ext = $attr->findExtension(AttrExt_DB::class);
            if ($ext !== null && $ext->isPrimaryKey()) {
                $entity->setValue($attr, $keys[$idx++]);
            }
        }

    }

    private function limitJoins(Query_Entities $query, Entity $entity): void
    {
        $idx = 0;
        foreach ($query->getJoins() as $join){
            if($join->join->hasLimit()){
                $entity->limitJoinedEntities($idx, $join->join->getLimitLow(), $join->join->getLimitMany());
            }
            foreach ($entity->getJoinedEntities($idx) as $joinedEntity){
                $this->limitJoins($join->join, $joinedEntity);
            }
            $idx++;
        }

    }

}
