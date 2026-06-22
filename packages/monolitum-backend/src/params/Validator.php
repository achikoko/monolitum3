<?php

namespace monolitum\backend\params;

use monolitum\model\AnonymousModel;
use monolitum\model\attr\Attr;
use monolitum\model\ValidatedValue;

interface Validator
{

    /**
     * @param AnonymousModel|string $model
     * @param Attr|string $attr
     * @param string|null $prefix
     * @param string|null $providerIfAnonymous
     * @return ValidatedValue
     */
    function validate(AnonymousModel|string $model, Attr|string $attr, ?string $prefix=null, ?string $providerIfAnonymous=null): ValidatedValue;

    /**
     * @param AnonymousModel|string $model
     * @param Attr|string $attr
     * @param string|null $prefix
     * @param string|null $providerIfAnonymous
     * @return ValidatedValue
     */
    function validateOnlyFormat(AnonymousModel|string $model, Attr|string $attr, ?string $prefix=null, ?string $providerIfAnonymous=null): ValidatedValue;

    function validateString(string $name, string $providerKey): ValidatedValue;

    /**
     * @param string $prefix
     * @return ValidatedValue
     */
    function validateKeyStartingWith_ReturnEnding(string $prefix, string $providerKey): ValidatedValue;

}
