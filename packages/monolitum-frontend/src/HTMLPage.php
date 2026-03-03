<?php

namespace monolitum\frontend;

use Closure;
use monolitum\core\MNode;
use monolitum\core\MObject;
use monolitum\frontend\html\HtmlBuilder;
use monolitum\frontend\html\HtmlElement;

class HTMLPage extends MNode {

    const HTML_VERSION_KEY = "html_version";
    const HTML_VERSION_VALUE_4 = "html";
    const HTML_VERSION_VALUE_5 = "html5";


    private array $pageConstants = [];

    /**
     * @var array<Head>
     */
    private array $head_components = [];

    /**
     * @var array<mixed>
     */
    private array $body_components = [];

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $htmlElementNodeExtensions = [];

//    /**
//     * @var array<Body>
//     */
//    private $body_components = [];

    private ?HtmlElementNode $body = null;

    function __construct(?Closure $builder = null){
        parent::__construct($builder);
    }

    /**
     * @return HtmlElementNode
     */
    public function getBody(): ?HtmlElementNode
    {
        return $this->body;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if ($object instanceof Head){
            $this->buildChildManually($object);
            $this->head_components[] = $object;
            return true;
        }else if($object instanceof HtmlElementNodeExtension){
            $this->htmlElementNodeExtensions[] = $object;
            return true;
        }else if($object instanceof MNode){
            $this->body_components[] = $object;
            return true;
        }else

            return parent::doAcceptChild($object);
    }

    /**
     * @param string $key
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getConstant(string $key, mixed $defaultValue=null): mixed
    {
        return key_exists($key, $this->pageConstants) ? $this->pageConstants[$key] : $defaultValue;
    }

    /**
     * TODO: Create constant manager
     * @param string $key
     * @param true $value
     * @return void
     */
    public function setConstant(string $key, mixed $value=true): void
    {
        $this->pageConstants[$key] = $value;
    }

    protected function onAfterBuild(): void
    {

        $this->body = new HtmlElementNode(new HtmlElement('body'), function (HtmlElementNode $it) {

            foreach ($this->htmlElementNodeExtensions as $child) {
                $it->doAcceptChild($child);
            }

            foreach ($this->body_components as $child) {
                $it->buildAndInsertChild($child);
            }

        });
        $this->buildChildManually($this->body);

        parent::onAfterBuild();

    }

    protected function onExecute(): void
    {
        foreach($this->head_components as $head_component){
            if($head_component instanceof Renderable_Node){
                $this->executeChildManually($head_component);
            }
        }
        parent::onExecute();
    }

    protected function onAfterExecute(): void
    {
        parent::onAfterExecute();

        $html = new HtmlElement('html');

        $head = new HtmlElement('head');
        foreach($this->head_components as $head_component){
            if($head_component instanceof Renderable_Node){
                Renderable_Node::renderRenderedTo($head_component, $head);
            }
        }
        $html->addChildElement($head);

        $this->executeChildManually($this->body);

        /** @var Rendered $renderedBody */
        $renderedBody = $this->body->render();
//        $renderedChildren = [];
//        foreach ($this->getChildren() as $child){
//            Renderable_Node::renderNonRenderableNode($child, $renderedChildren);
//        }
//
//        Renderable_Node::renderRenderedTo($renderedChildren, $renderedBody->getSingle());
        Renderable_Node::renderRenderedTo($renderedBody, $html);


        $htmlBuilder = new HtmlBuilder();

        $this->onBeforeEcho($html);
        echo '<!DOCTYPE html>';
        echo $htmlBuilder->render($html);
        $this->onAfterEcho();

    }

    /**
     * Called before starting the echo of the recently built and executed page
     * @param HtmlElement $html
     * @return void
     */
    protected function onBeforeEcho(HtmlElement $html): void
    {

    }

    /**
     * Called after finishing the echo of the executed page
     * @return void
     */
    protected function onAfterEcho(): void
    {

    }

}
