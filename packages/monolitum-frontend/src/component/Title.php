<?php

namespace monolitum\frontend\component;

use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;
use monolitum\i18n\TS;

class Title extends Head{

    public function __construct(public string|TS $string, $builder = null)
    {
        parent::__construct($builder);
    }

    protected function onAfterBuild(): void
    {
        $this->string = TS::unwrapAuto($this->string);
    }

    public function render(): Renderable|array|null
    {
        return Rendered::of(
            new HtmlElement("title", $this->string)
        );
    }

}
