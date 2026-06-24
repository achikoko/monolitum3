<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_File;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class ParamsProvider_POST extends ParamsProvider_FromGlobalArray
{

    public function __construct()
    {
        parent::__construct($_POST);
    }

    public function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ValidatedValue
    {
        if ($name === null){
            $name = $attr->getId();
        }

        // Handling files here
        if ($attr instanceof Attr_File) {

            return isset($_FILES[$name]) ? $attr->validate($_FILES[$name]) : new ValidatedValue(true);
        } else {

            // Null values are values
            if (array_key_exists($name, $this->globalArray)) {
                return $attr->validate($this->globalArray[$name]);
            } else {
                return new ValidatedValue(true);
            }

        }
    }

}
