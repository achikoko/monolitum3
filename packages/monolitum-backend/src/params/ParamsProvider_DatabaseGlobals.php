<?php

namespace monolitum\backend\params;

use monolitum\core\panic\DevPanic;
use monolitum\database\Query;
use monolitum\database\Query_Not;
use monolitum\database\Query_Or;
use monolitum\model\attr\Attr;
use monolitum\model\EntitiesManager;
use monolitum\model\Model;

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

    public function setPrefixModelUnionString(string $prefixModelUnionString = "__"): self
    {
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

    function retrieveModelAttribute(Model $model, Attr $attr, ?string $name = null): ?string
    {
        $this->assureModel();

        if ($name === null){
            $name = $attr->getId();
        }

        if($this->prefixModelUnionString) {
            $name = $this->model->id . $this->prefixModelUnionString . $name;
        }

        $entity = Query::newQuery($this->model)->select($this->value->getId())->filter([$this->key->getId() => $name])->execute()->firstAndClose();

        return $entity?->getString($this->value);
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
