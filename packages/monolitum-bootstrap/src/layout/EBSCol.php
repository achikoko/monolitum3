<?php

namespace monolitum\bootstrap\layout;

use Closure;
use monolitum\bootstrap\style\BSCol;
use monolitum\bootstrap\style\BSColSpan;
use monolitum\bootstrap\style\BSColSpanResponsive;
use monolitum\frontend\component\Div;

class EBSCol extends Div
{
    private BSCol $layout;

    private ?BSColSpanResponsive $colSpanResponsive = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->layout = BSCol::of();
    }

    public function span(int|BSColSpan|BSColSpanResponsive $span): self
    {
        $this->colSpanResponsive = null;
        $this->layout->span($span);
        return $this;
    }

    public function def(BSColSpan|int $def): self
    {
        return $this->xs($def);
    }

    public function xs(BSColSpan|int $xs): self
    {
        if($this->colSpanResponsive === null){
            $this->colSpanResponsive = BSColSpanResponsive::xs($xs);
        }else{
            $this->colSpanResponsive->def($xs);
        }
        return $this;
    }

    public function sm(BSColSpan|int $sm): self
    {
        if($this->colSpanResponsive === null){
           $this->colSpanResponsive = BSColSpanResponsive::xs();
        }
        $this->colSpanResponsive->sm($sm);
        return $this;
    }

    public function md(BSColSpan|int $md): self
    {
        if($this->colSpanResponsive === null){
            $this->colSpanResponsive = BSColSpanResponsive::xs();
        }
        $this->colSpanResponsive->md($md);
        return $this;
    }

    public function lg(BSColSpan|int $lg): self
    {
        if($this->colSpanResponsive === null){
            $this->colSpanResponsive = BSColSpanResponsive::xs();
        }
        $this->colSpanResponsive->lg($lg);
        return $this;
    }

    public function xl(BSColSpan|int $xl): self
    {
        if($this->colSpanResponsive === null){
            $this->colSpanResponsive = BSColSpanResponsive::xs();
        }
        $this->colSpanResponsive->xl($xl);
        return $this;
    }

    public function xxl(BSColSpan|int $xxl): self
    {
        if($this->colSpanResponsive === null){
            $this->colSpanResponsive = BSColSpanResponsive::xs();
        }
        $this->colSpanResponsive->xxl($xxl);
        return $this;
    }

    protected function onAfterBuild(): void
    {
        if($this->colSpanResponsive !== null){
            $this->layout->span($this->colSpanResponsive);
        }
        $this->layout->buildInto($this);
        parent::onAfterBuild();
    }
}
