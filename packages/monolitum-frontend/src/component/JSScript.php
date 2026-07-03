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

    private ?Path $path = null;

    private ?ResResolver $pathResolver = null;

    /**
     * @var null|string|bool
     */
    private null|string|bool $module;

    /**
     * @var bool
     */
    private bool $async;

    private ?string $inline = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct($builder);
    }

    public function setSrc(Path $path, null|string|bool $module = null, bool $async = false): self
    {
        $this->path = $path;
        $this->module = $module;
        $this->async = $async;
        $this->inline = null;
        return $this;
    }

    /**
     * @param string|null $inline
     */
    public function setInline(?string $inline): void
    {
        $this->path = null;
        $this->inline = $inline;
    }



    protected function onBuild(): void
    {
        parent::onBuild();
        if($this->path !== null) {
            $active = new Request_ResResolver($this->path);
            $active->setEncodeUrl(false);
            Monolitum::getInstance()->push($active);
            $this->pathResolver = $active->getResResolver();
        }
    }

    public function render(): Renderable|array|null
    {
        $link = new HtmlElement("script");

        if($this->pathResolver !== null) {
            $link->setAttribute("src", $resolved = $this->pathResolver->resolve());
            if ($this->module)
                $link->setAttribute("type", "module");

            if ($this->async)
                $link->setAttribute("async", "true");

            if (is_string($this->module)) {

                $importmap = new HtmlElement("script");
                $importmap->setAttribute("type", "importmap");
                $importmap->setContent(new HtmlElementContent('{"imports": {"' . $this->module . '": "' . $resolved . '"}}', true));

                return Rendered::of([$importmap, $link]);

            }

        }else if($this->inline !== null) {
            $link->setRawContent($this->inline);
        }

        return Rendered::of($link);

    }

    public static function of(Path $path, ?Closure $builder = null): static
    {
        return new self(function (self $it) use ($builder, $path) {
            $it->setSrc($path);
            if($builder !== null) call_user_func($builder);
        });
    }

}
