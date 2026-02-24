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
     * @param bool $anonymousIsPost
     * @return ValidatedValue
     */
    function validate(AnonymousModel|Model|string $model, Attr|string $attr, ?string $prefix=null, ?bool $anonymousIsPost=null): ValidatedValue;

    /**
     * @param string $name
     * @return ValidatedValue
     */
    function validateStringPost(string $name): ValidatedValue;

    /**
     * @param string $prefix
     * @return ValidatedValue
     */
    function validateStringPost_NameStartingWith_ReturnEnding(string $prefix): ValidatedValue;

}
