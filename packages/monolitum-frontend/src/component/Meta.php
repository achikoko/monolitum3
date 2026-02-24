<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\backend\params\Path;
use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;

class Meta extends Head{

    private string $name;

    private string $content;

    public function __construct(string $name, string $content, ?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->name = $name;
        $this->content = $content;
    }

    public function render(): Renderable|array|null
    {
        $link = new HtmlElement("meta");
        $link->setAttribute("name", $this->name);
        $link->setAttribute("content", $this->content);

        return Rendered::of($link);
    }

    public static function of(string $name, string $content, ?Closure $builder = null): static
    {
        return new self($name, $content, $builder);
    }

}
