<?php

namespace monolitum\bootstrap\component;

use Closure;
use monolitum\backend\params\Path;
use monolitum\backend\resources\Request_ResResolver;
use monolitum\backend\resources\ResResolver;
use monolitum\core\Monolitum;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable;

class IFrame extends HtmlElementNode
{


    private Path|string|null $source = null;

    private ?ResResolver $sourceResolver = null;

    private ?string $type = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct((new HtmlElement("iframe"))->setRequireEndTag(), $builder);
    }

    /**
     * TODO svg
     * @param string $path
     * @return $this
     */
    public function type(string $path): self
    {
        $this->type = $path;
        return $this;
    }

    /**
     * TODO svg
     * @param string|Path $path
     * @return $this
     */
    public function source(string|Path $path): self
    {
        $this->source = $path;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        if($this->source instanceof Path){
            $active = new Request_ResResolver($this->source);
            Monolitum::getInstance()->push($active);
            $this->sourceResolver = $active->getResResolver();
        }

        parent::onAfterBuild();
    }

    public function render(): array|null|Renderable
    {
        $img = $this->getElement();

        if($this->sourceResolver){
            $img->setAttribute('src', $this->sourceResolver->resolve());
        }else if(is_string($this->source)){
            $img->setAttribute('src', $this->source);
        }

        if(is_string($this->type))
            $img->setAttribute('type', $this->type);

        return parent::render();
    }

}
