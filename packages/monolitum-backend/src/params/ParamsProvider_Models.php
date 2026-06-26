<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

interface ParamsProvider_Models extends ParamsProvider
{

    /**
     * Validates the format of a value given its Attr.
     * @param Model $model
     * @param Attr $attr
     * @param string|null $name
     * @return ValidatedValue
     */
    public function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ValidatedValue;

    /**
     * Instances an entity and fills it with the values of the model after validating them.
     * @param Model $model
     * @param bool $writable
     * @return Entity|null
     */
    public function retrieveModel(Model $model, bool $writable = false): ?Entity;

}
