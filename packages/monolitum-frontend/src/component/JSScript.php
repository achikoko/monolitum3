<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\backend\params\Path;
use monolitum\backend\resources\Request_ResResolver;
use monolitum\backend\resources\ResResolver;
use monolitum\core\Monolitum;
use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;

class JSScript extends Head{

    private Path $path;

    private ResResolver $pathResolver;

    /**
     * @var null|string|bool
     */
    private null|string|bool $module;

    /**
     * @var bool
     */
    private bool $async;

    public function __construct(Path $path, null|string|bool $module = null, bool $async = false, ?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->module = $module;
        $this->path = $path;
        $this->async = $async;
    }

    protected function onBuild(): void
    {
        $active = new Request_ResResolver($this->path);
        $active->setEncodeUrl(false);
        Monolitum::getInstance()->push($active);
        $this->pathResolver = $active->getResResolver();
        parent::onBuild();
    }

    public function render(): Renderable|array|null
    {
        $link = new HtmlElement("script");
        $link->setAttribute("src", $resolved = $this->pathResolver->resolve());
        if($this->module)
            $link->setAttribute("type", "module");

        if($this->async)
            $link->setAttribute("async", "true");

        if(is_string($this->module)){

            $importmap = new HtmlElement("script");
            $importmap->setAttribute("type", "importmap");
            $importmap->setContent((new HtmlElementContent('{"imports": {"' . $this->module . '": "' . $resolved . '"}}'))->setRaw());

            return Rendered::of([$importmap, $link]);

        }else{
            return Rendered::of($link);
        }

    }

    public static function of(Path $path, ?Closure $builder = null): static
    {
        return new self($path, builder: $builder);
    }

}
