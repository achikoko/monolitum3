<?php
namespace monolitum\model;

use DateTime;
use monolitum\core\panic\DevPanic;
use monolitum\model\attr\Attr;

abstract class Entity
{

    private Model $model;

    /**
     * @var array<string, mixed>
     */
    private array $values = [];

    private bool $protectWrite = false;

    private ?EntityPersister $manager = null;

    private ?array $updateAttrs = null;

    protected bool $hasBeenNotified = false;

    public function _setModel(Model $model): void
    {
        $this->model = $model;
    }

    public function getModel(): Model
    {
        return $this->model;
    }

    public function getAttr($attr)
    {
        if(is_string($attr))
            $attr = $this->model->getAttr($attr);
        return $attr;
    }

    /**
     * @param string|Attr $attr
     * @param mixed $value
     * @return $this
     */
    private function _set(Attr|string $attr, mixed $value): self
    {
        if($this->protectWrite)
            throw new DevPanic("Entity is not writable.");
        if($attr instanceof Attr)
            $attr = $attr->getId();
        $this->values[$attr] = $value;
        if ($this->updateAttrs !== null) {
            $this->updateAttrs[$attr] = $value;
        }
        if (!$this->hasBeenNotified && $this->manager != null) {
            $this->manager->_notifyEntityChanged($this);
            $this->hasBeenNotified = true;
        }
        return $this;
    }

    /**
     * If manager is set, then it is notified if the entity is updated
     */
    public function _setManager(EntityPersister $entityManager): void
    {
        $this->manager = $entityManager;
        $this->updateAttrs = [];
    }

    public function getString(Attr|string $attr): ?string
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        return key_exists($attr, $this->values) ? $this->values[$attr] : null;
    }

    public function setString(Attr|string $attr, ?string $string): self
    {
        return $this->_set($attr, $string);
    }

    public function getInt(Attr|string $attr): ?int
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        return key_exists($attr, $this->values) ? $this->values[$attr] : null;
    }

    public function setInt(Attr|string $attr, ?int $int): self
    {
        return $this->_set($attr, $int);
    }

    public function getBool(Attr|string $attr): ?bool
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        return key_exists($attr, $this->values) ? $this->values[$attr] : null;
    }

    /**
     * @param Attr|string $attr
     * @param bool $bool
     * @return $this
     */
    public function setBool(Attr|string $attr, ?bool $bool): self
    {
        return $this->_set($attr, $bool);
    }

    public function getDate(Attr|string $attr): ?DateTime
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        return key_exists($attr, $this->values) ? $this->values[$attr] : null;
    }

    /**
     * @param Attr|string $attr
     * @param DateTime $date
     * @return $this
     */
    public function setDate(Attr|string $attr, ?DateTime $date): self
    {
        return $this->_set($attr, $date);
    }

    public function getValue(Attr|string $attr): mixed
    {
        if($attr instanceof Attr)
            $attr = $attr->getId();
        return key_exists($attr, $this->values) ? $this->values[$attr] : null;
    }

    public function setValue(Attr|string $attr, mixed $value): self
    {
        return $this->_set($attr, $value);
    }

    abstract function buildModel(EntitiesManager $manager): Model;

    public function _protectWrite(): void
    {
        $this->protectWrite = true;
    }

    public function hasValue(Attr|string $attr): bool
    {
        if($attr instanceof Attr)
            return key_exists($attr->getId(), $this->values);
        return key_exists($attr, $this->values);
    }

    /**
     * @return array|null
     */
    public function getUpdateAttrs(): ?array
    {
        return $this->updateAttrs;
    }

    public function update(): void
    {
        $this->manager->_executeUpdateEntity($this);
    }

    /**
     * @return int|false
     */
    public function insert(): false|int
    {
        $returned = $this->manager->_executeInsertEntity($this);
        return $returned[1];
    }

    public function delete(): void
    {
        $this->manager->_executeDeleteEntity($this);
    }

    public static function instance(bool $forInsert = false, Entity $cloneOf = null): static
    {
        return EntitiesManager::findSelf()->instance(static::class, $forInsert, $cloneOf);
    }

}

