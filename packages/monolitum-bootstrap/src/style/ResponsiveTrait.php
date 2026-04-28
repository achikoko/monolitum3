<?php

namespace monolitum\bootstrap\style;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;

trait ResponsiveTrait
{

    protected ?ResponsiveProperty $def = null;

    protected ?ResponsiveProperty $sm = null;

    protected ?ResponsiveProperty $md = null;

    protected ?ResponsiveProperty $lg = null;

    protected ?ResponsiveProperty $xl = null;

    protected ?ResponsiveProperty $xxl = null;

    protected function _buildInto(HtmlElementNode|HtmlElement $component, ?string $prefix, bool $inverted = false): void
    {

        if($this->def != null)
            $this->_buildIntoForBreakpoint($component, $prefix, null, $this->def, $inverted);

        if($this->sm != null)
            $this->_buildIntoForBreakpoint($component, $prefix, "sm", $this->sm, $inverted);

        if($this->md != null)
            $this->_buildIntoForBreakpoint($component, $prefix, "md", $this->md, $inverted);

        if($this->lg != null)
            $this->_buildIntoForBreakpoint($component, $prefix, "lg", $this->lg, $inverted);

        if($this->xl != null)
            $this->_buildIntoForBreakpoint($component, $prefix, "xl", $this->xl, $inverted);

        if($this->xxl != null)
            $this->_buildIntoForBreakpoint($component, $prefix, "xxl", $this->xxl, $inverted);

    }

    protected function _buildIntoForBreakpoint(HtmlElementNode|HtmlElement $component, string $prefix, ?string $breakpoint, ResponsiveProperty $responsiveProperty, bool $inverted = false): void
    {

        if($breakpoint != null){
            $component->addClass($prefix . "-" . $breakpoint . "-" . $responsiveProperty->getValue($inverted));
        }else{
            $component->addClass($prefix . "-" . $responsiveProperty->getValue($inverted));
        }

    }

}
