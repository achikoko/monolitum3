<?php

namespace monolitum\model;

use monolitum\i18n\TS;

class PostProcessStringContext
{

    private bool $resultValid = true;
    private TS|string|null $resultError = null;

    private bool $isPostProcessed = false;
    private ?string $postProcessResult = null;

    public function __construct(public string $value)
    {

    }

    public function invalidate(TS|string|array|null $errorMessage): void
    {
        $this->resultValid = false;
        $this->resultError = is_array($errorMessage) ? TS::from($errorMessage) : $errorMessage;
    }

    public function overwrite(?string $postProcessResult): void
    {
        $this->isPostProcessed = true;
        $this->postProcessResult = $postProcessResult;
    }

    public function getResultValid(): bool
    {
        return $this->resultValid;
    }

    public function getResultError(): string|TS|null
    {
        return $this->resultError;
    }

    /**
     * @return bool
     */
    public function isPostProcessed(): bool
    {
        return $this->isPostProcessed;
    }

    /**
     * @return string|null
     */
    public function getPostProcessResult(): ?string
    {
        return $this->postProcessResult;
    }

}
