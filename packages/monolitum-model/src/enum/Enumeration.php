<?php

namespace monolitum\model\enum;

use IteratorAggregate;
use monolitum\core\panic\DevPanic;
use monolitum\i18n\TS;
use Traversable;
use UnitEnum;

class Enumeration implements IteratorAggregate
{

    const ENUM_VALUE_TUPLE_KEY = 0;
    const ENUM_VALUE_TUPLE_LABEL = 1;
    const ENUM_VALUE_TUPLE_GROUP = 2;

    /** @var array<EnumGroup> */
    private array $enumGroups = [];

    /**
     * @var array<string|int, string|TS>
     */
    private array $enumKeyIndexes = [];

    /**
     * @var array<array{string|UnitEnum, string|TS, ?int}> tuples of <$key, $label, $group>
     */
    private array $enumValues = [];

    public function __construct(){

    }

    public function appendGroup(string|TS $label): self
    {
        $currentBuildingEnum = new EnumGroup(count($this->enumGroups), $label);
        $currentBuildingEnum->setLabel($label);
        $currentBuildingEnum->setIndexRange(count($this->enumValues));
        $this->enumGroups[] = $currentBuildingEnum;
        return $this;
    }

    public function appendValue(string|int|UnitEnum $key, string|TS $label): self
    {
        if(is_string($key) || is_int($key)){
            $this->enumKeyIndexes[$key] = count($this->enumValues);
            $this->enumValues[] = [$key, $label, count($this->enumGroups)-1];
        }else{
            $this->enumKeyIndexes[$key->name] = count($this->enumValues);
            $this->enumValues[] = [$key, $label, count($this->enumGroups)-1];
        }
        return $this;
    }

    /**
     * @return array
     */
    public function getEnumValues(): array
    {
        return $this->enumValues;
    }

    public function getIterator(): Traversable
    {
        return new EnumerationIterator($this);
    }

    public function getLabel(mixed $key): string|TS|null
    {
        if(key_exists($key, $this->enumKeyIndexes)){
            return $this->enumValues[$this->enumKeyIndexes[$key]][self::ENUM_VALUE_TUPLE_LABEL];
        }else if($key instanceof UnitEnum) {
            foreach ($this->enumValues as $enumValue){
                $tupleKey = $enumValue[self::ENUM_VALUE_TUPLE_KEY];
                if($tupleKey instanceof UnitEnum){
                    if($tupleKey === $key) return $enumValue[self::ENUM_VALUE_TUPLE_LABEL];
                }
            }
        }else {
            foreach ($this->enumValues as $enumValue){
                $tupleKey = $enumValue[self::ENUM_VALUE_TUPLE_KEY];
                if($tupleKey instanceof UnitEnum){
                    if($tupleKey->name === $key) return $enumValue[self::ENUM_VALUE_TUPLE_LABEL];
                }else{
                    if($tupleKey === $key) return $enumValue[self::ENUM_VALUE_TUPLE_LABEL];
                }
            }
        }

        return null;
    }

    public function keyExist(mixed $key): bool
    {
        if(key_exists($key, $this->enumKeyIndexes)){
            return true;
        }else if($key instanceof UnitEnum) {
            foreach ($this->enumValues as $enumValue){
                $tupleKey = $enumValue[self::ENUM_VALUE_TUPLE_KEY];
                if($tupleKey instanceof UnitEnum){
                    if($tupleKey === $key) return true;
                }
            }
        }else {
            foreach ($this->enumValues as $enumValue){
                $tupleKey = $enumValue[self::ENUM_VALUE_TUPLE_KEY];
                if($tupleKey instanceof UnitEnum){
                    if($tupleKey->name === $key) return true;
                }else{
                    if($tupleKey === $key) return true;
                }
            }
        }

        return false;
    }

    public static function fromArray(array $enums): self
    {
        $enumeration = new self();
        foreach ($enums as $itemKey => $itemValue) {

            if (is_array($itemValue)) {
                $enumeration->appendValue($itemValue[0], $itemValue[1]);
            } else if ($itemValue instanceof UnitEnum) {
                $enumeration->appendValue($itemValue->name, $itemValue->name);
            }else if (is_string($itemValue) || $itemValue instanceof TS) {
                $enumeration->appendValue($itemKey, $itemValue);
            } else {
                throw new DevPanic("Enum constant not found");
            }
        }
        return $enumeration;
    }

    public static function wrap(array|Enumeration $enum): Enumeration
    {
        if(is_array($enum)){
            return self::fromArray($enum);
        }else{
            return $enum;
        }
    }

    public function getGroupOfKey(int|string $itemKey): ?EnumGroup
    {
        if(key_exists($itemKey, $this->enumKeyIndexes)){
            $groupIndex = $this->enumValues[$this->enumKeyIndexes[$itemKey]][self::ENUM_VALUE_TUPLE_GROUP];
            return $groupIndex < 0 ? null : $this->enumGroups[$groupIndex];
        }
    }

}
