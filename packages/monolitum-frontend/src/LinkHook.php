<?php

namespace monolitum\frontend;

use monolitum\frontend\html\HtmlElement;

interface LinkHook
{
    /**
     * @param Renderable_Node $component This parameter let the hook grab info about the component. If it is bootstrap or not, etc.
     * @param HtmlElement $element Optional 'a' or 'button' element.
     * @return void
     */
    public function buildLinkHook(Renderable_Node $component, HtmlElement $element = null): void;

    /**
     * @param Renderable_Node $component This parameter let the hook grab info about the component. If it is bootstrap or not, etc.
     * @param HtmlElement $element Mandatory 'a' or 'button' element.
     * @return void
     */
    public function renderLinkHook(Renderable_Node $component, HtmlElement $element): void;

}
