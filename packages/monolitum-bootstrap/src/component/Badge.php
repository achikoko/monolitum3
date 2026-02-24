<?php

namespace monolitum\bootstrap\component;

use monolitum\bootstrap\style\BSBackground;
use monolitum\bootstrap\values\BSColor;
use monolitum\frontend\component\AbstractTextNode;
use monolitum\frontend\html\HtmlElement;
use monolitum\i18n\TS;

class Badge extends AbstractTextNode
{

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("span", null), $builder);
        $this->addClass("badge");
    }

    public static function of(TS|string $content, BSColor $color): static
    {
        return new static(function (Badge $badge) use ($content, $color) {
            $badge->setContent($content);
            $badge->color($color);
        });
    }

    public function color(BSColor $color): self
    {
        BSBackground::of($color)->pushSelf();
        return $this;
    }

}
