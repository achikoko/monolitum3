<?php

namespace monolitum\frontend\form;

use monolitum\backend\params\Source;
use monolitum\backend\params\Validator;
use monolitum\model\AnonymousModel;
use monolitum\model\attr\Attr;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

class Form_Validator_Entity extends Form_Validator
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var class-string|AnonymousModel|Model
     */
    private string|AnonymousModel|Model $model;

    /**
     * @var ?Entity
     */
    private ?Entity $currentEntity = null;

    private Source $sourceIfAnonymousModel;

    /**
     * @param Validator $validator
     * @param class-string|AnonymousModel|Model $model
     * @param mixed|null $post
     */
    public function __construct(Validator $validator, string|AnonymousModel|Model $model, ?Source $sourceIfAnonymousModel = null)
    {
        $this->validator = $validator;
        $this->model = $model;
        $this->sourceIfAnonymousModel = $sourceIfAnonymousModel ?? Source::POST;
    }

    /**
     * @param ?Entity $currentEntity
     * @return self
     */
    public function setCurrentEntity(?Entity $currentEntity): self
    {
        $this->currentEntity = $currentEntity;
        return $this;
    }

    public function _validateAll(): void
    {
        parent::_validateAll();

        if(is_string($this->model))
            $this->model = Model::pushFindByName($this->model);

        foreach($this->model->getAttrs() as $attr){

            // Skip attributes without Form specification
            $ext = $attr->findExtension(AttrExt_Form::class);
            if($ext === null)
                continue;

            if($this->isValidatable($attr)){

                $validatedValue = $this->getValidatedValue($attr);

                if($validatedValue !== null && !$validatedValue->isValid()){
                    $this->build_allValid = false;
                }

            }

        }

    }

    /**
     * @param Attr|string $attr
     * @return ?ValidatedValue
     */
    function getValidatedValue(Attr|string $attr): ?ValidatedValue
    {
        // Retrieve model
        $this->model = Model::pushFindByName($this->model);

        // Retrieve attribute
        $attr = $this->model->getAttr($attr);

        if(key_exists($attr->getId(), $this->overwritten_validatedValues)){
            // The attr has been overwritten
            $validatedValue = $this->overwritten_validatedValues[$attr->getId()];
        }else if(key_exists($attr->getId(), $this->build_validatedValues)){
            // The attr has been already validated
            $validatedValue = $this->build_validatedValues[$attr->getId()];
        }else{

            // Validate the value that comes from outside
            $validatedValue = $this->validator->validate($this->model, $attr, $this->form->_getValidatePrefix(), $this->sourceIfAnonymousModel);

            // If not valid, try to substitute with a valid value
            if(!$validatedValue->isValid()){

                // Skip attribute without Form specification
                /** @var AttrExt_Form $ext */
                $ext = $attr->findExtension(AttrExt_Form::class);
                if($ext !== null && $ext->isSubstituteNotValid()){
                    $value = $ext->getDef();
                    $validatedValue = new ValidatedValue(true, true, $ext->getDef(), $attr->stringValue($value));
                }

            }else if($validatedValue->isNull()){
                // Skip attribute without Form specification
                /** @var AttrExt_Form $ext */
                $ext = $attr->findExtension(AttrExt_Form::class);
                if($ext !== null && $ext->isSubstituteNullValues()){
                    $value = $ext->getDef();
                    $validatedValue = new ValidatedValue(true, true, $ext->getDef(), $attr->stringValue($value));
                }

            }

            // These lines are commented, because the value is not valid
            // Set the current value in editing entity if not valid
//            if(!$validatedValue->isValid() && $this->currentEntity !== null)
//                $validatedValue = new ValidatedValue(true, true, $this->currentEntity->getValue($attr));

            // Store it if the value must be validated, if not, then the dev only wanted to check some foreign value.
            if($this->isValidatable($attr)){
                $this->build_validatedValues[$attr->getId()] = $validatedValue;
            }

        }

        return $validatedValue;
    }

    /**
     * @param Attr|string $attr
     * @return ValidatedValue
     */
    public function getDefaultValue(Attr|string $attr): ValidatedValue
    {

        // Retrieve model
        $this->model = Model::pushFindByName($this->model);

        // Retrieve attribute
        $attr = $this->model->getAttr($attr);

        if($this->currentEntity !== null){
            $value = $this->currentEntity->getValue($attr);
            return new ValidatedValue(true, true, $value, null, $attr->stringValue($value));
        }

        // Skip attribute without Form specification
        /** @var AttrExt_Form $ext */
        $ext = $attr->findExtension(AttrExt_Form::class);
        if($ext !== null && $ext->isDefaultSet()){
            $value = $ext->getDef();
            return new ValidatedValue(true, true, $ext->getDef(), $attr->stringValue($value));
        }

        return new ValidatedValue(false);

    }

    /**
     * @param string $prefix
     * @return ValidatedValue|null
     */
    public function validateSubmissionKey(string $prefix): ValidatedValue
    {
        return $this->validator->validateStringPost_NameStartingWith_ReturnEnding($prefix);
    }

    public function validateString(string $key, Source $source = Source::POST): ValidatedValue
    {
        return $this->validator->validateString($key, $source);
    }



    /**
     * @param Entity $entity
     * @return void
     */
    public function writeValidValuesOn(Entity $entity): void
    {
        $this->model = Model::pushFindByName($this->model);

        foreach($this->model->getAttrs() as $attr){
            if(!$this->isValidatable($attr))
                continue;
            $validatedValue = $this->build_validatedValues[$attr->getId()];
            if($validatedValue !== null && $validatedValue->isValid())
                $entity->setValue($attr, $validatedValue->getValue());
        }

    }

    /**
     * @param Attr|string $attrId
     * @return Attr
     */
    public function getAttr(Attr|string $attrId): Attr
    {
        $this->model = Model::pushFindByName($this->model);
        return $this->model->getAttr($attrId);
    }

}
