<?php

namespace monolitum\model;

interface FileTypeValidator
{
    /**
     * Receives a ValidatedValue with a valid File in it, returns null if the file is not the expected type of the
     * validator or a ValidatedValue to tell the file is the expected type and (or not) valid.
     * @param ValidatedValue $file
     * @return ValidatedValue|null
     */
    function validate(ValidatedValue $validatedValue): ?ValidatedValue;
}
