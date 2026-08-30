<?php
namespace monolitum\frontend\form;

class AttrExt_Form_String extends AttrExt_Form
{

    private bool $password = false;

    private bool $searchable = false;

    private ?string $inputType = null;

    public function password(): self
    {
        $this->password = true;
        return $this;
    }

    public function searchable(bool $searchable=true): self
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function inputType(string $string): self
    {
        $this->inputType = $string;
        return $this;
    }

    public function isPassword(): bool
    {
        return $this->password;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getInputType(): ?string
    {
        return $this->inputType;
    }

}

