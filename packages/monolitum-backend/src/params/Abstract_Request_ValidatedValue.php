<?php

namespace monolitum\backend\params;

use monolitum\core\MObject;
use monolitum\model\ValidatedValue;

class Abstract_Request_ValidatedValue implements MObject, ValidatedValueGetter
{
    const TYPE_STRING = "str";
    const TYPE_INT = "int";

    //const TYPE_CODED_INT = "int";

    private ValidatedValue $validatedValue;

    public function __construct(public readonly string $type)
    {

    }

    /**
     * @param ValidatedValue $validatedValue
     */
    public function setValidatedValue(ValidatedValue $validatedValue): void
    {
        $this->validatedValue = $validatedValue;
    }

    /**
     * @return ValidatedValue
     */
    public function getValidatedValue(): ValidatedValue
    {
        return $this->validatedValue;
    }

    function onNotReceived(): void
    {
        $this->validatedValue = new ValidatedValue(false); // TODO default parameter
    }
}
