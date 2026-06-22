<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_File;
use monolitum\model\Model;

class ParamsProvider_POST extends ParamsProvider_FromGlobalArray
{

    public function __construct()
    {
        parent::__construct($_POST);
    }

    function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ?string
    {
        if ($name === null){
            $name = $attr->getId();
        }

        // Handling files here
        if ($attr instanceof Attr_File) {

            return $_FILES[$name] ?? null;

        } else {

            // Null values are values
            if (array_key_exists($name, $this->globalArray)) {
                return $this->globalArray[$name];
            } else {
                return null;
            }

        }
    }

}
