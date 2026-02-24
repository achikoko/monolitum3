<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;

class AllowedExtension
{

    private ResourceProviderManager $manager;

    private ?string $mimeType = null;

    public function prepare(ResourceProviderManager $manager): void
    {
        $this->manager = $manager;
    }

    public function getManager(): ResourceProviderManager
    {
        return $this->manager;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function readLineByLine(): bool
    {
        return false;
    }

    public function getRewriter(Path $path): ?callable
    {
        return null;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;
        return $this;
    }

}
