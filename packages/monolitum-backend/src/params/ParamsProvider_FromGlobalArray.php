<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class ParamsProvider_FromGlobalArray implements ParamsProvider_Strings, ParamsProvider_Models, ParamsProvider_SupportsKeySeeking
{

    public function __construct(protected array &$globalArray)
    {

    }

    public function retrieveParam(string $param): ?string
    {
        if(isset($this->globalArray[$param])){
            return $this->globalArray[$param];
        }
        return null;
    }

    function retrieveParams(array &$returnArray, array|null $paramsSelection, array $exceptions): void
    {
        if($paramsSelection === null){
            foreach ($this->globalArray as $key => $value){
                if(!in_array($key, $exceptions)){
                    $returnArray[$key] = $value;
                }
            }
        }else{
            foreach ($paramsSelection as $key){
                if(isset($this->globalArray[$key])){
                    $returnArray[$key] = $this->globalArray[$key];
                }
            }
        }
    }

    public function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ValidatedValue
    {
        if ($name === null){
            $name = $attr->getId();
        }

        if (array_key_exists($name, $this->globalArray)) {
            return $attr->validate($this->globalArray[$name]);
        } else {
            return new ValidatedValue(true);
        }
    }

    public function retrieveModel(Model $model, bool $writable = false): ?Entity
    {
        $entity = EntitiesManager::findSelf()->instance($model);
        foreach ($model->getAttrs() as $attr){
            $validatedValue = $this->retrieveModelAttribute($model, $attr);
            if($validatedValue->isValid()){
                $entity->setValue($attr, $validatedValue);
            }
        }
        return $entity;
    }

    public function validateKeyStartingWith_ReturnEnding(string $prefix): ?string
    {
        $prefixLength = strlen($prefix);
        foreach ($this->globalArray as $name => $value){

            // php <8 starts_with
            if(strncmp($name, $prefix, $prefixLength) === 0){
                $actionLength = strlen($name) - $prefixLength;
                if($actionLength === 0)
                    return ""; // Have to distinct "" than null

                return substr( $name, $prefixLength, $actionLength);

            }

        }
        return null;
    }

}
