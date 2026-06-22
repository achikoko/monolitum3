<?php

namespace monolitum\backend\params;

use Closure;
use monolitum\core\Find;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\model\AnonymousModel;
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_File;
use monolitum\model\AttrExt_Validate;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class ParamsManager extends MNode implements Validator
{

    private ?string $defaultProvider = null;

    /**
     * @var array<string, ParamsProvider>
     */
    private array $providers = [];

    /**
     * @var array<string, string> model id -> provider
     */
    private array $models = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function registerProvider(string $providerKey, ParamsProvider $provider, bool $default = false): void
    {
        $this->providers[$providerKey] = $provider;
        if($default){
            $this->defaultProvider = $providerKey;
        }
    }

    /**
     * @param class-string|Model $model
     * @param string $providerKey
     * @return void
     */
    public function addModel(string|Model $model, string $providerKey): void
    {
        if(!isset($this->providers[$providerKey])){
            throw new DevPanic("Provider '$providerKey' is not registered.");
        }
        if(is_string($model))
            $model = Model::pushFindByName($model);
        $this->models[$model->getIdOrClass()] = $providerKey;
    }

    /**
     * @param Attr $attr
     * @param string $name
     * @param array $globalArray
     * @return ValidatedValue
     */
    public function validateAttributeFromGlobalArray(Attr $attr, string $name, array $globalArray): ValidatedValue
    {
        if ($attr instanceof Attr_File) {

            $value = array_key_exists($name, $_FILES) ? $_FILES[$name] : null;

            if ($value !== null) {
                if (
                    !isset($value['error']) ||
                    is_array($value['error'])
                ) {
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_BAD_FORMAT);
                }
                if ($value['error'] == UPLOAD_ERR_NO_FILE)
                    $value = null;
            }

            return $attr->validate($value);

        } else {

            if (array_key_exists($name, $globalArray)) {
                return $attr->validate($globalArray[$name]);
            } else {
                return new ValidatedValue(true, true, null);
            }

        }
    }

    public function doReceive(MObject $object): bool
    {
        if($object instanceof Request_FindParameters){

            /** @var array<string, string> $returnArray */
            $returnArray = [];

            if(is_string($object->providerOrProviders)) {
                if (isset($this->providers[$object->providerOrProviders])) {
                    $provider = $this->providers[$object->providerOrProviders];
                    if ($provider instanceof ParamsProvider_Strings) {
                        $provider->retrieveParams($returnArray, $object->paramsSelection, $object->exceptions);
                    }
                }
            } else if(is_array($object->providerOrProviders)){
                foreach ($object->providerOrProviders as $category){
                    if(isset($this->providers[$category])){
                        $provider = $this->providers[$category];
                        if($provider instanceof ParamsProvider_Strings){
                            $provider->retrieveParams($returnArray, $object->paramsSelection, $object->exceptions);
                        }
                    }
                }
            }

            $object->setFoundParams($returnArray);

            return true;
        }else if($object instanceof Request_Parameter_ValidatedValue){
            $validatedValue = $this->validate($object->model, $object->attr);
            $object->setValidatedValue($validatedValue);
            return true;
        }

        return parent::doReceive($object);
    }

    public function validate(string|AnonymousModel $model, Attr|string $attr, ?string $prefix = null, ?string $providerIfAnonymous=null): ValidatedValue
    {
        $validatedValue = $this->validateOnlyFormat($model, $attr, $prefix, $providerIfAnonymous);

        if(!$validatedValue->isWellFormat()){
            return $validatedValue;
        }

        if(!$model instanceof AnonymousModel) {
            $model = Model::pushFindByName($model);
        }

        $attr = $model->getAttr($attr);

        /** @var AttrExt_Validate|null $attrExt_Validate */
        $attrExt_Validate = $attr->findExtension(AttrExt_Validate::class);

        if($attrExt_Validate !== null){
            $validatedValue = $attrExt_Validate->validate($validatedValue);
        }

        return $validatedValue;

    }

    public function validateOnlyFormat(AnonymousModel|string $model, Attr|string $attr, ?string $prefix=null, ?string $providerIfAnonymous = null): ValidatedValue
    {

        if ($model instanceof AnonymousModel){
            $attr = $model->getAttr($attr);

            if($model instanceof Model){
                $providerKey = $this->models[$model->getIdOrClass()] ?? null;
                if($providerKey === null){
                    throw new DevPanic("Model {$model->getIdOrClass()} is not registered as params.");
                }
            }else{

                $providerKey = $providerIfAnonymous;

                if($providerKey === null){
                    $providerKey = $this->defaultProvider;
                }

                if($providerKey === null){
                   throw new DevPanic("Default anonymous provider was not resolved.");
                }

            }

        }else{
            /** @var Model $model */
            $model = Model::pushFindByName($model);
            $providerKey = $this->models[$model->getIdOrClass()] ?? null;
            if($providerKey === null){
                throw new DevPanic("Model {$model->getIdOrClass()} is not registered as params.");
            }
        }

        $provider = $this->providers[$providerKey];

        $attr = $model->getAttr($attr);

        /** @var AttrExt_Param|null $attrExt_Param */
        $attrExt_Param = $attr->findExtension(AttrExt_Param::class);
        if($attrExt_Param != null){
            $name = $attrExt_Param->getName();
        }else{
            $name = $attr->getId();
        }
        if($prefix !== null)
            $name = $prefix . $name;

        if($model instanceof Model && $provider instanceof ParamsProvider_Models){
            $result = $provider->retrieveModelAttribute($model, $attr, $name);
        }else if($provider instanceof ParamsProvider_Strings){
            $result = $provider->retrieveParam($name);
        }else{
            throw new DevPanic();
        }

        if($result === null){
            return new ValidatedValue(true, true, null);
        }else{
            return $attr->validate($result);
        }

    }

    function multiple(array $_files, bool $top = true): array
    {
        $files = array();
        foreach($_files as $name=>$file){
            if($top) $sub_name = $file['name'];
            else    $sub_name = $name;

            if(is_array($sub_name)){
                foreach(array_keys($sub_name) as $key){
                    $files[$name][$key] = array(
                        'name'     => $file['name'][$key],
                        'type'     => $file['type'][$key],
                        'tmp_name' => $file['tmp_name'][$key],
                        'error'    => $file['error'][$key],
                        'size'     => $file['size'][$key],
                    );
                    $files[$name] = $this->multiple($files[$name], FALSE);
                }
            }else{
                $files[$name] = $file;
            }
        }
        return $files;
    }

    public function validateString(string $name, string $providerKey): ValidatedValue
    {

        $provider = $this->providers[$providerKey];

        if($provider instanceof ParamsProvider_Strings){
            $result = $provider->retrieveParam($name);
        }else{
            throw new DevPanic();
        }

        if(is_string($result) || is_numeric($result)){
            return new ValidatedValue(true, true, strval($result), null, strval($result));
        }else if(is_null($result)){
            return new ValidatedValue(true, true, null, null, "null");
        }

        return new ValidatedValue(false);

    }

    public function validateKeyStartingWith_ReturnEnding(string $prefix, string $providerKey): ValidatedValue
    {

        $provider = $this->providers[$providerKey];

        if($provider instanceof ParamsProvider_SupportsKeySeeking){
            $result = $provider->validateKeyStartingWith_ReturnEnding($prefix);
        }else{
            throw new DevPanic();
        }

        if($result !== null){
            return new ValidatedValue(true, true, strval($result), null, strval($result));
        }else{
            return new ValidatedValue(true, true, null, null, "null");
        }

    }

    /**
     * @param string|Model $model
     * @param string|Attr $attr
     * @return ValidatedValue
     */
    public static function pushGetParameterValidatedValue(Model|string $model, Attr|string $attr): ValidatedValue
    {
        /** @var ParamsManager $varManager */
        $varManager = Find::pushAndGet(ParamsManager::class);
        return $varManager->validate($model, $attr);
    }

    public static function pushModel(Model|string $class, string $providerKey): void
    {
        ParamsManager::findSelf()->addModel($class, $providerKey);
    }

    public static function pushModelToGET(Model|string $class): void
    {
        ParamsManager::findSelf()->addModel($class, StandardProvider::GET);
    }

    public static function pushModelToPOST(Model|string $class): void
    {
        ParamsManager::findSelf()->addModel($class, StandardProvider::POST);
    }

}
