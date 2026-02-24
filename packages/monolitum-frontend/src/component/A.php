<?php

namespace monolitum\frontend\component;

use Closure;
use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\backend\resources\HrefResolver;
use monolitum\backend\resources\HrefResolver_String;
use monolitum\backend\resources\Request_HrefResolver;
use monolitum\core\Monolitum;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\LinkHook;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;

class A extends AbstractTextNode
{

    private Path|string|Link|LinkHook|null $href = null;

    private ?HrefResolver $hrefResolver = null;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("a"), $builder);
    }

    public function setHref(LinkHook|Link|string|Path|null $href): void
    {
        $this->href = $href;
    }

    protected function onAfterBuild(): void
    {

        if($this->href !== null){
            if(is_string($this->href)){
                $this->hrefResolver = new HrefResolver_String($this->href);
            }else if($this->href instanceof LinkHook){
                $this->href->buildLinkHook($this, $this->getElement());
            }else{
                $active = new Request_HrefResolver($this->href);
                Monolitum::getInstance()->push($active);
                $this->hrefResolver = $active->getHrefResolver();
            }
        }

        parent::onAfterBuild();
    }

    public function render(): Renderable|array|null
    {
        $a = $this->getElement();

        if($this->hrefResolver !== null){
            $a->setAttribute("href", $this->hrefResolver->resolve());
        }else if($this->href instanceof LinkHook){
            $this->href->renderLinkHook($this, $this->getElement());
        }else{
            $a->setAttribute("href", "#");
        }

        return parent::render();
    }

    public static function fromContent(string|Renderable_Node $content, LinkHook|Link|string|Path $href): A
    {
        $fc = new A();
        $fc->append($content);
        $fc->setHref($href);
        return $fc;
    }

    public static function of(?Closure $builder = null): A
    {
        return new A($builder);
    }

}
