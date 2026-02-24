<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\backend\params\Path;
use monolitum\backend\resources\Request_ResResolver;
use monolitum\backend\resources\ResResolver;
use monolitum\core\Monolitum;
use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;

class CSSLink extends Head {

    private ?ResResolver $pathResolver = null;

    public function __construct(public readonly Path $path, ?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    protected function onBuild(): void
    {
        $active = new Request_ResResolver($this->path);
        Monolitum::getInstance()->push($active);
        $this->pathResolver = $active->getResResolver();
        parent::onBuild();
    }

    public function render(): Renderable|array|null
    {
        $link = new HtmlElement("link");
        $link->setAttribute("rel", "stylesheet");
        $link->setAttribute("href", $this->pathResolver->resolve());

        return Rendered::of($link);
    }

    public static function of(Path $path, ?Closure $builder = null): static
    {
        return new self($path, $builder);
    }

}
