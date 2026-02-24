<?php
namespace monolitum\frontend\css;

interface CSSProperty
{
    public function write(): string;
}
