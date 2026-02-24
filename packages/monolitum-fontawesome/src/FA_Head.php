<?php

namespace monolitum\fontawesome;

use monolitum\backend\params\Path;
use monolitum\frontend\component\CSSLink;
use monolitum\frontend\Head;

class FA_Head extends Head
{

    public function __construct($builder = null)
    {
        parent::__construct($builder);
    }

    protected function onBuild(): void
    {
        CSSLink::of(Path::from("monolitum", "fontawesome", "res", "css", "all.min.css"))->pushSelf();
//        CSSLink::of(Path::from("monolitum", "fontawesome", "res", "css", "fontawesome.css"))->pushSelf();
        parent::onBuild();
    }

    function onNotReceived()
    {
        // TODO: Implement onNotReceived() method.
    }
}
