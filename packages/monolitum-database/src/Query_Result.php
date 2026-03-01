<?php

namespace monolitum\database;

use monolitum\core\Find;
use monolitum\core\util\MClosableIterator;
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_Bool;
use monolitum\model\attr\Attr_Date;
use monolitum\model\attr\Attr_DateTime;
use monolitum\model\attr\Attr_Decimal;
use monolitum\model\attr\Attr_Int;
use monolitum\model\attr\Attr_String;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\EntitiesManager;
use PDO;
use PDOStatement;

class Query_Result implements MClosableIterator
{

    private EntitiesManager $entityManager;

    private ?Entity $nextRow = null;

    private ?int $iteratorKey = null;

    private bool $finished = false;

    /**
     * @param DatabaseManager $manager
     * @param Model $model
     * @param array<Attr> $select
     * @param bool $protectForUpdate
     * @param PDOStatement $stmt
     */
    public function __construct(
        DatabaseManager $manager,
        private readonly Model $model,
        private readonly array $select,
        private readonly bool $protectForUpdate,
        private readonly PDOStatement $stmt)
    {

        $this->entityManager = Find::pushAndGetFrom(EntitiesManager::class, $manager);
    }

    public function hasNext(): bool
    {
        if($this->finished)
            return false;
        if($this->nextRow != null)
            return true;
        $this->nextRow = $this->next();
        return $this->nextRow !== null;
    }

    /**
     * @return Entity|null
     */
    public function next(): mixed
    {
        if($this->finished)
            return null;

        if($this->nextRow != null){
            $ret = $this->nextRow;
            $this->nextRow = null;
            return $ret;
        }

        $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        if($row === false){
            $this->close();
            return null;
        }

        $entity = $this->entityManager->instance($this->model);

        foreach ($this->select as $attr){

            $rowValue = $row[$attr->getId()];

            if($rowValue !== null){

                if($attr instanceof Attr_String){
                    $entity->setString($attr, strval($rowValue));
                }else if($attr instanceof Attr_Int){
                    $entity->setInt($attr, intval($rowValue));
                }else if($attr instanceof Attr_Decimal){
                    $entity->setInt($attr, intval($rowValue));
                }else if($attr instanceof Attr_Bool){
                    if(is_int($rowValue))
                        $rowValue = $rowValue != 0;
                    else if($rowValue === "true")
                        $rowValue = true;
                    else if($rowValue === "false")
                        $rowValue = false;
                    $entity->setBool($attr, $rowValue);
                }else if($attr instanceof Attr_Date || $attr instanceof Attr_DateTime){
                    $entity->setDate($attr, date_create($rowValue));
                }else if($attr instanceof DatabaseableAttr){
                    $entity->setValue($attr, $attr->parseValue($rowValue));
                }

            }

        }

        if($this->protectForUpdate){
            $entity->_setManager($this->entityManager);
        }else{
            $entity->_protectWrite();
        }

        if(is_null($this->iteratorKey))
            $this->iteratorKey = 0;
        else
            $this->iteratorKey++;

        return $entity;

    }

    public function firstAndClose(): mixed
    {
        $entity = $this->next();
        $this->close();
        return $entity;
    }

    /**
     *
     */
    public function close(): void
    {
        $this->finished = true;
        $this->stmt->closeCursor();
    }

    #[\ReturnTypeWillChange]
    public function current(): ?Entity
    {
        return $this->nextRow;
    }

    #[\ReturnTypeWillChange]
    public function key(): int
    {
        return $this->iteratorKey;
    }

}
