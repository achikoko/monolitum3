<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace monolitum\frontend\html;

use monolitum\core\panic\DevPanic;
use monolitum\frontend\Renderable;

/**
 * Html Element Content Class
 *
 * @package    HtmlBuilder
 * @author     Sven Sanzenbacher
 */
class HtmlElementContent implements Renderable
{

    public function __construct(public string $content, public bool $raw = false)
    {

    }

    function renderTo(?HtmlElement $element): void
    {
        if($element instanceof HtmlElement)
            $element->addChildElement($this);
    }

    function onNotReceived()
    {
        throw new DevPanic("HtmlElementContent was not received.");
    }
}
