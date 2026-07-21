<?php

namespace monolitum\model;

use monolitum\i18n\TS;

class PostValidatorContext
{

    public readonly mixed $value;

    private bool $resultValid = true;
    private TS|string|null $resultError = null;

    public function __construct(public readonly ValidatedValue $validatedValue)
    {
        $this->value = $validatedValue->getValue();
    }

    public function invalidate(TS|string|array|null $errorMessage): void
    {
        $this->resultValid = false;
        $this->resultError = is_array($errorMessage) ? TS::from($errorMessage) : $errorMessage;
    }

    public function getResultValid(): bool
    {
        return $this->resultValid;
    }

    public function getResultError(): string|TS|null
    {
        return $this->resultError;
    }

}
