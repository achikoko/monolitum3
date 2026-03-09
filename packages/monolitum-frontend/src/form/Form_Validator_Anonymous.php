<?php

namespace monolitum\frontend\form;

use monolitum\backend\params\Source;
use monolitum\backend\params\Validator;
use monolitum\core\panic\DevPanic;
use monolitum\model\attr\Attr;
use monolitum\model\ValidatedValue;

class Form_Validator_Anonymous extends Form_Validator
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @param Validator $validator
     */
    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param Attr|string $attr
     * @return ?ValidatedValue
     */
    function getValidatedValue(Attr|string $attr): ?ValidatedValue
    {
        return new ValidatedValue(false);
    }

    /**
     * @param Attr|string $attr
     * @return ValidatedValue
     */
    public function getDefaultValue(Attr|string $attr): ValidatedValue
    {

        if($attr instanceof Attr) {
            // Skip attribute without Form specification
            /** @var AttrExt_Form $ext */
            $ext = $attr->findExtension(AttrExt_Form::class);
            if ($ext !== null && $ext->isDefaultSet()) {
                $value = $ext->getDef();
                return new ValidatedValue(true, true, $ext->getDef(), $attr->stringValue($value));
            }
        }

        return new ValidatedValue(false);

    }

    /**
     * @param string $prefix
     * @return ValidatedValue
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
     * @param Attr|string $attrId
     * @return Attr
     */
    public function getAttr(Attr|string $attrId): Attr
    {
        throw new DevPanic("getAttr() is not supported in this validator");
    }

}
