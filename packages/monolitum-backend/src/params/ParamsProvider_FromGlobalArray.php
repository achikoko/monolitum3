<?php

namespace monolitum\backend\params;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

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

    function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ?string
    {
        if ($name === null){
            $name = $attr->getId();
        }

        if (array_key_exists($name, $this->globalArray)) {
            return $this->globalArray[$name];
        } else {
            return null;
        }
    }

    public function validateKeyStartingWith_ReturnEnding(string $prefix): ?string
    {
        $prefixLength = strlen($prefix);
        foreach ($this->globalArray as $name => $value){

            // php <8 starts_with
            if(strncmp($name, $prefix, $prefixLength) === 0){
                $actionLength = strlen($name) - $prefixLength;
                if($actionLength === 0)
                    return null;

                $action = substr( $name, $prefixLength, strlen($name) - $prefixLength);

                return $action;
            }

        }
        return null;
    }

}
