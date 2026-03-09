<?php

namespace monolitum\backend\params;

use monolitum\model\AnonymousModel;
use monolitum\model\attr\Attr;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

interface Validator
{

    /**
     * @param AnonymousModel|Model|string $model
     * @param Attr|string $attr
     * @param string|null $prefix
     * @param Source|null $sourceIfAnonymous
     * @return ValidatedValue
     */
    function validate(AnonymousModel|Model|string $model, Attr|string $attr, ?string $prefix=null, ?Source $sourceIfAnonymous=null): ValidatedValue;

    /**
     * @param AnonymousModel|Model|string $model
     * @param Attr|string $attr
     * @param string|null $prefix
     * @param Source|null $sourceIfAnonymous
     * @return ValidatedValue
     */
    function validateOnlyFormat(AnonymousModel|Model|string $model, Attr|string $attr, ?string $prefix=null, ?Source $sourceIfAnonymous=null): ValidatedValue;

    function validateString(string $name, Source $source = Source::POST): ValidatedValue;

    /**
     * @param string $prefix
     * @return ValidatedValue
     */
    function validateStringPost_NameStartingWith_ReturnEnding(string $prefix): ValidatedValue;

}
