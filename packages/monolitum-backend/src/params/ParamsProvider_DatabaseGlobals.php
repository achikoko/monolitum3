<?php

namespace monolitum\backend\params;

use monolitum\core\panic\DevPanic;
use monolitum\database\Query;
use monolitum\database\Query_Like;
use monolitum\database\Query_Not;
use monolitum\database\Query_Or;
use monolitum\model\attr\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class ParamsProvider_DatabaseGlobals implements ParamsProvider_Strings, ParamsProvider_Models
{

    private ParamsProvider_DatabaseGlobals_Model $modelOfModel;

    public ?Model $model = null;

    public Attr $key;
    public Attr $value;

    private ?string $prefixModelUnionString = null;

    public function __construct()
    {

    }

    /**
     * @return string|null
     */
    public function getPrefixModelUnionString(): ?string
    {
        return $this->prefixModelUnionString;
    }

    public function setPrefixModelUnionString(string $prefixModelUnionString = "__"): self
    {
        if(str_contains($prefixModelUnionString, "?")) {
            throw new DevPanic("'?' sign not supported in ParamsProvider_DatabaseGlobals");
        }
        $this->prefixModelUnionString = $prefixModelUnionString;
        return $this;
    }

    public function setModel(ParamsProvider_DatabaseGlobals_Model $model): self
    {
        $this->modelOfModel = $model;
        return $this;
    }

    function retrieveParam(string $param): ?string
    {
        if($this->prefixModelUnionString)
            throw new DevPanic("Cannot receive string because Model is needed.");
        $this->assureModel();
        $entity = Query::newQuery($this->model)->select($this->value)->filter([$this->key->getId() => $param])->execute()->firstAndClose();
        // TODO cache this
        return $entity?->getString($this->value);
    }

    function retrieveParams(array &$returnArray, ?array $paramsSelection, array $exceptions): void
    {
        if($this->prefixModelUnionString)
            throw new DevPanic("Cannot receive string because Model is needed.");
        $this->assureModel();

        if($paramsSelection === null){

            if(empty($exceptions)){

                $result = Query::newQuery($this->model)
                    ->select([$this->key, $this->value])
                    ->execute();

            }else{

                $result = Query::newQuery($this->model)
                    ->select([$this->key, $this->value])
                    ->filter([$this->key->getId() => new Query_Not($exceptions)])
                    ->execute();

            }

        }else{

            $result = Query::newQuery($this->model)
                ->select([$this->key, $this->value])
                ->filter([$this->key->getId() => new Query_Or($paramsSelection)])
                ->execute();

        }

        foreach ($result as $entity){
            $key = $entity->getString($this->key);
//                if(!in_array($key, $exceptions)){
            $returnArray[$key] = $entity?->getString($this->value);
//                }
        }
    }

    function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ValidatedValue
    {
        $this->assureModel();

        if ($name === null){
            $name = $attr->getId();
        }

        if($this->prefixModelUnionString && $model->id) {
            $name = $model->id . $this->prefixModelUnionString . $name;
        }

        $entity = Query::newQuery($this->model)->select($this->value->getId())->filter([$this->key->getId() => $name])->execute()->firstAndClose();

        return $entity ? $attr->validate($entity->getString($this->value)) : new ValidatedValue();
    }

    public function retrieveModel(Model $model, bool $writable = false): ?Entity
    {
        $this->assureModel();
        if($this->prefixModelUnionString && $model->id){
            if(str_contains($model->id, "?")) {
                throw new DevPanic("'?' sign not supported in ParamsProvider_DatabaseGlobals");
            }
            $result = Query::newQuery($this->model)
                ->filter([$this->key->getId() => new Query_Like("??%", $model->id, $this->prefixModelUnionString)])
                ->execute();
        }else{
            $result = Query::newQuery($this->model)->select($this->value->getId())->execute();
        }

        $entity = EntitiesManager::findSelf()->instance($model);
        foreach ($result as $resultLine){
            $key = $resultLine->getString($this->key);
            $value = $resultLine->getString($this->value);

            if($this->prefixModelUnionString){
                $key = substr($key, strlen($model->id . $this->prefixModelUnionString));
            }

            $attr = $model->getAttr($key);

            $validatedValue = $attr->validate($value);
            if($validatedValue->isValid()){
                $entity->setValue($attr, $value);
            }

        }

        if($writable){
            $entity->_setManager(new ParamsProvider_DatabaseGlobals_Persister($this, $model));
        }

        return $entity;
    }

    private function assureModel(): void
    {
        if($this->model === null){
            $this->model = EntitiesManager::findSelf()->getModel($this->modelOfModel->model);
            $this->key = $this->model->getAttr($this->modelOfModel->key);
            $this->value = $this->model->getAttr($this->modelOfModel->value);
        }
    }

}
