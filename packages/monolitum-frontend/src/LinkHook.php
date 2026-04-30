<?php

namespace monolitum\frontend;

use monolitum\frontend\html\HtmlElement;

/**
 * A link hook is created on the reactor component and given to a TRIGGER (a button, or a form that can produce events).
 */
interface LinkHook
{
    /**
     * The TRIGGER calls this method at build time, to ask for the capabilities of the hook.
     *
     * If the LinkHookMode returned by this method is $preferredMode == LinkHookMode::RENDER_JAVASCRIPT. The TRIGGER expects
     * this method to generate the necessary JavaScript code at this exact moment and appended to the parent tree of
     * $triggerComponent as a "script" tag or so...
     *
     * @param Renderable_Node $triggerComponent The component that will trigger the hook on an event.
     * @param LinkHookMode $preferredMode The TRIGGER preferred mode. If it is a (bootstrap) button or anchor, this value
     *      will be LinkHookMode::MODIFY_RECEIVER, because tipically actions are done by adding attributes to the TRIGGER.
     * @param array $extra Data set from the TRIGGER. It may contain kind of event, expected parameters...
     * @param HtmlElement|null $element If $preferredMode == LinkHookMode::MODIFY_RECEIVER. The element that will be received to be modified.
     * @return LinkHookMode|null The response and final decision of the hook. If null, the hook is not compatible with the receiver.
     */
    public function buildLinkHook(Renderable_Node $triggerComponent, LinkHookMode $preferredMode, array $extra, HtmlElement $element = null): ?LinkHookMode;

    /**
     * Called only if $preferredMode == LinkHookMode::MODIFY_RECEIVER.
     * @param Renderable_Node $renderable_Node This parameter let the hook grab info about the component. If it is bootstrap or not, etc.
     * @param array $extra Data set from the TRIGGER. It may contain kind of event, expected parameters...
     * @param HtmlElement $element Element to be modified.
     * @return void
     */
    public function renderLinkHookIntoElement(Renderable_Node $renderable_Node, array $extra, HtmlElement $element): void;

    /**
     * Called only if $preferredMode == LinkHookMode::RENDER_JAVASCRIPT.
     * @param Renderable_Node $renderable_Node This parameter let the hook grab info about the component. If it is bootstrap or not, etc.
     * @param array $extra Data set from the TRIGGER. It may contain kind of event, expected parameters...
     * @return string The JAVASCRIPT that triggers the action. (It may be copied inside a onevent="..." attribute)
     */
    public function renderLinkHookIntoJavascript(Renderable_Node $renderable_Node, array $extra): string;

}
