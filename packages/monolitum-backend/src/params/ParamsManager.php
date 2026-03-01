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

    //** @var Model[] by name */
    //private $sessionModels = [];

    /** @var Model[] by name */
    private array $getModels = [];

    /** @var Model[] by name */
    private array $postModels = [];

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    /**
     * @param Attr $attr
     * @param $name
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

            if(is_string($object->category))
                $this->fill_params($returnArray, $object->category, $object->paramsSelection, $object->exceptions);
            else if(is_array($object->category)){
                foreach ($object->category as $category){
                    $this->fill_params($returnArray, $category, $object->paramsSelection, $object->exceptions);
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

    /**
     * @param array<string, string> $returnArray
     * @param string $category
     * @param null|string[] $paramsSelection
     * @param string[] $exceptions
     * @return void
     */
    private function fill_params(array &$returnArray, string $category, array|null $paramsSelection, array $exceptions): void
    {

        $master = match ($category) {
            Request_FindParameters::CATEGORY_GET => $_GET,
            Request_FindParameters::CATEGORY_POST => $_POST,
            default => [],
        };

        if($paramsSelection === null){
            foreach ($master as $key => $value){
                if(!in_array($key, $exceptions)){
                    $returnArray[$key] = $value;
                }
            }
        }else{
            foreach ($paramsSelection as $key){
                if(key_exists($key, $master)){
                    $returnArray[$key] = $master[$key];
                }
            }
        }

    }

    public function validate(string|AnonymousModel|Model $model, Attr|string $attr, ?string $prefix = null, ?Source $sourceIfAnonymous=null): ValidatedValue
    {
        /** @var Model $model */
        if(is_string($model))
            $model = Model::pushFindByName($model);
        $attr = $model->getAttr($attr);

        $validatedValue = $this->validateOnlyFormat($model, $attr, $prefix, $sourceIfAnonymous);

//        if(!$validatedValue->isValid()){

            /** @var AttrExt_Validate|null $attrExt_Validate */
            $attrExt_Validate = $attr->findExtension(AttrExt_Validate::class);

            if($attrExt_Validate !== null){
                $validatedValue = $attrExt_Validate->validate($validatedValue);
            }

//        }

        return $validatedValue;

    }

//    public function validateOnlyFormatAnonymous(AnonymousModel $model, Attr|string $attr, ?string $prefix, ?bool $post): ValidatedValue
//    {
//
//        $attr = $model->getAttr($attr);
//
//        if($post)
//            $globalArray = $_POST;
//        else
//            $globalArray = $_GET;
//
//        /** @var AttrExt_Param|null $attrExt_Param */
//        $attrExt_Param = $attr->findExtension(AttrExt_Param::class);
//        if($attrExt_Param != null){
//            $name = $attrExt_Param->getName();
//        }else{
//            $name = $attr->getId();
//        }
//        if($prefix !== null)
//            $name = $prefix . $name;
//
//        return $this->validateAttributeFromGlobalArray($attr, $name, $globalArray);
//    }

    public function validateOnlyFormat(AnonymousModel|string $model, Attr|string $attr, ?string $prefix=null, ?Source $sourceIfAnonymous = null): ValidatedValue
    {
        /** @var Model $model */
        if(is_string($model))
            $model = Model::pushFindByName($model);
        $attr = $model->getAttr($attr);

        if ($model instanceof Model){

            if(array_key_exists($model->getIdOrClass(), $this->postModels)){
//                if($sourceIfAnonymous !== null && $sourceIfAnonymous !== Source::POST)
//                    throw new DevPanic("Called validateOnlyFormat() with a Model and source restriction: " . $model->getIdOrClass() . ". Models source must be set in advance.");

                $globalArray = $_POST;
            } else if(array_key_exists($model->getIdOrClass(), $this->getModels)){
//                if($sourceIfAnonymous !== null && $sourceIfAnonymous !== Source::GET)
//                    throw new DevPanic("Called validateOnlyFormat() with a Model and source restriction: " . $model->getIdOrClass() . ". Models source must be set in advance.");

                $globalArray = $_GET;
            } else {
                throw new DevPanic("No declared model as params: " . $model->getIdOrClass() . ".");
            }

        }else{

            $sourceIfAnonymous = $sourceIfAnonymous ?? Source::GET;
            $globalArray = match($sourceIfAnonymous){
                Source::GET => $_GET,
                Source::POST => $_POST,
                default => throw new DevPanic("Not supported params method: " . $sourceIfAnonymous->value)
            };

        }

        /** @var AttrExt_Param|null $attrExt_Param */
        $attrExt_Param = $attr->findExtension(AttrExt_Param::class);
        if($attrExt_Param != null){
            $name = $attrExt_Param->getName();
        }else{
            $name = $attr->getId();
        }
        if($prefix !== null)
            $name = $prefix . $name;

        return $this->validateAttributeFromGlobalArray($attr, $name, $globalArray);
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

    /**
     * @param string $name
     * @return ValidatedValue
     */
    public function validateStringPost(string $name): ValidatedValue
    {

        $globalArray = $_POST;

        $value = array_key_exists($name, $globalArray) ? $globalArray[$name] : null;

        if(is_string($value) || is_numeric($value)){
            return new ValidatedValue(true, true, strval($value), null, strval($value));
        }else if(is_null($value)){
            return new ValidatedValue(true, true, null, null, "null");
        }

        return new ValidatedValue(false);

    }

    public function validateStringPost_NameStartingWith_ReturnEnding(string $prefix): ValidatedValue
    {

        $globalArray = $_POST;

        foreach ($globalArray as $name => $value){

            $prefixLength = strlen($prefix);

            // php <8 starts_with
            if(strncmp($name, $prefix, $prefixLength) === 0){
                $actionLength = strlen($name) - $prefixLength;
                if($actionLength === 0)
                    return new ValidatedValue(true, true, null, null, "null");

                $action = substr( $name, $prefixLength, strlen($name) - $prefixLength);

                return new ValidatedValue(true, true, strval($action), null, strval($action));
            }

        }

        return new ValidatedValue(false);

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

    public static function pushModelToGET(Model|string $class): void
    {
        /** @var ParamsManager $manager */
        $manager = Find::pushAndGet(ParamsManager::class);
        $manager->addModel_GET($class);

    }

    public static function pushModelToPOST(Model|string $class): void
    {
        /** @var ParamsManager $manager */
        $manager = Find::pushAndGet(ParamsManager::class);
        $manager->addModel_POST($class);

    }

    /**
     * @param string|Model $class
     */
    public function addModel_GET(Model|string $class): void
    {
        $model = Model::pushFindByName($class);
        $this->getModels[$model->getIdOrClass()] = $model;
    }

    /**
     * @param string|Model $class
     */
    public function addModel_POST(Model|string $class): void
    {
        $model = Model::pushFindByName($class);
        $this->postModels[$model->getIdOrClass()] = $model;
    }


}
