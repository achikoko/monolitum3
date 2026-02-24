<?php

namespace monolitum\bootstrap\style;

use monolitum\core\panic\DevPanic;
use monolitum\frontend\HtmlElementNodeExtension;

class BSPosition extends HtmlElementNodeExtension
{

    private function __construct(private array $classes)
    {
        parent::__construct();
    }

    public static function relative(): BSPosition
    {
        return new self(["position-relative"]);
    }

    public static function absolute(): BSPosition
    {
        return new self(["position-absolute"]);
    }

    public function top(?bool $inside=true): BSPosition
    {
        if ($inside === null) {
            $this->classes[] = "top-0";
            $this->classes[] = "translate-middle-y";
        } else if ($inside){
            $this->classes[] = "top-0";
        } else {
            $this->classes[] = "bottom-100";
        }
        return $this;
    }

    public function bottom(?bool $inside=true): BSPosition
    {
        if ($inside === null) {
            $this->classes[] = "top-100";
            $this->classes[] = "translate-middle-y";
        } else if ($inside){
            $this->classes[] = "bottom-0";
        } else {
            $this->classes[] = "top-100";
        }
        return $this;
    }

    public function start(?bool $inside=true): BSPosition
    {
        if ($inside === null) {
            $this->classes[] = "start-0";
            $this->classes[] = "translate-middle-x";
        } else if ($inside){
            $this->classes[] = "start-0";
        } else {
            $this->classes[] = "end-100";
        }
        return $this;
    }

    public function end(?bool $inside=true): BSPosition
    {
        if ($inside === null) {
            $this->classes[] = "start-100";
            $this->classes[] = "translate-middle-x";
        } else if ($inside){
            $this->classes[] = "end-0";
        } else {
            $this->classes[] = "start-100";
        }
        return $this;
    }

    public function middle(?bool $fromTop=null): BSPosition
    {
        if ($fromTop === null) {
            $this->classes[] = "top-50";
            $this->classes[] = "translate-middle-y";
        } else if ($fromTop){
            $this->classes[] = "top-50";
        } else {
            $this->classes[] = "bottom-50";
        }
        return $this;
    }

    public function center(?bool $fromStart=null): BSPosition
    {
        if ($fromStart === null) {
            $this->classes[] = "start-50";
            $this->classes[] = "translate-middle-x";
        } else if ($fromStart){
            $this->classes[] = "end-50";
        } else {
            $this->classes[] = "start-50";
        }
        return $this;
    }

    public function apply(): void
    {
        $this->getElementComponent()->addClass(...$this->classes);
    }

    public function getValue(bool $inverted = false): string
    {
        throw new DevPanic("NO");
    }
}
