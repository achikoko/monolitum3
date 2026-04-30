<?php

namespace monolitum\bootstrap\component;

use Closure;
use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\backend\resources\HrefResolver;
use monolitum\backend\resources\Request_HrefResolver;
use monolitum\bootstrap\form\BSFormSubmit;
use monolitum\core\MObject;
use monolitum\core\Monolitum;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\AbstractTextNode;
use monolitum\frontend\form\Form;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\frontend\LinkHook;
use monolitum\frontend\LinkHookMode;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

class BSButton extends AbstractTextNode
{
    use TraitBSButton;

    private ?LinkHook $linkHook = null;

    private Path|string|null|Link $pathOrLink = null;

    private ?Closure $onAction = null;

    private ?HrefResolver $hrefResolver = null;

    private bool $disabled = false;

    private bool $post = false;

    private ?Form $form = null;

    private BSFormSubmit $formSubmit;

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $extensions = [];

    private ?LinkHookMode $finalLinkHookMode;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement(null), $builder);
    }

    public function doAcceptChild(MObject $object): bool
    {
        // Intercept extensions
        if($object instanceof HtmlElementNodeExtension){
            $this->extensions[] = $object;
            return true;
        }
        return parent::doAcceptChild($object);
    }

    public function setOnAction(Closure $onAction): self
    {
        $this->onAction = $onAction;
        $this->linkHook = null;
        $this->pathOrLink = null;
        return $this;
    }

    public function setLink(LinkHook|Link|string|Path $link): self
    {
        $this->onAction = null;

        if($link instanceof LinkHook){
            $this->linkHook = $link;
            $this->pathOrLink = null;
        }else{
            $this->linkHook = null;
            $this->pathOrLink = $link;
        }
        return $this;
    }

    /**
     * @param bool $post
     * @return void
     */
    public function setPost(bool $post=true): self
    {
        $this->post = $post;
        return $this;
    }

    public function setDisabled(bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        if($this->linkHook !== null){
            $this->finalLinkHookMode = $this->linkHook->buildLinkHook($this, LinkHookMode::MODIFY_RECEIVER, [], $this->getElement());
        }else if($this->onAction !== null) {
            // instance Form if post is set, Form has to be autodisabled when another form is in a parent.
            // when

            if($this->disabled){

                throw new DevPanic("Not supported");

            }else{

                $this->form = Form::fromAnonymousModelAndId($this->getId(), function (Form $it) {
                    $it->addClass("d-inline-block");
                    $it->receive($this->formSubmit = new BSFormSubmit(function (BSFormSubmit $it) {
                        foreach ($this->extensions as $extension) {
                            $it->receive($extension);
                        }
                        if($this->getColor() !== null){
                            $it->color($this->getColor(), $this->isOutline());
                        }
                        $it->setIsLarge($this->getIsLarge());
                        $it->setIsLinkStyled($this->isLinkStyled());

                        $buttonId = $this->getId();
                        if($buttonId !== null){
                            $it->setId($buttonId);
                        }
                        $it->addClass(...$this->getClasses());

                    }));

                    $it->setOnValidated(function (Form $it){
                        if($it->isAllValid()) { // If csrf token is not valid, function is called but this if is not executed
                            $onAction = $this->onAction;
                            $onAction($this); // $this as BSButton
                        }
                    });

                });
                $this->buildChildManually($this->form);
            }

        }else if(!is_string($this->pathOrLink) && $this->post) {
            // instance Form if post is set, Form has to be autodisabled when another form is in a parent.
            // when

            if($this->disabled){

                throw new DevPanic("Not supported");

            }else{

                $this->form = Form::fromAnonymousModelAndId($this->getId(), function (Form $it) {
                    $it->setLink($this->pathOrLink);

                    $it->receive($this->formSubmit = new BSFormSubmit(function (BSFormSubmit $it) {
                        foreach ($this->extensions as $extension) {
                            $it->receive($extension);
                        }

                        if($this->getColor() !== null){
                            $it->color($this->getColor(), $this->isOutline());
                        }
                        $it->setIsLarge($this->getIsLarge());
                        $it->setIsLinkStyled($this->isLinkStyled());

                        $buttonId = $this->getId();
                        if($buttonId !== null){
                            $it->setId($buttonId);
                        }
                        $it->addClass(...$this->getClasses());

                    }));

                });
                $this->buildChildManually($this->form);
            }

        }else if($this->pathOrLink !== null && !is_string($this->pathOrLink)){
            $active = new Request_HrefResolver($this->pathOrLink);
            Monolitum::getInstance()->push($active);
            $this->hrefResolver = $active->getHrefResolver();
        }

        if($this->form === null){
            foreach ($this->extensions as $extension) {
                parent::doAcceptChild($extension);
            }
        }

        parent::onAfterBuild();
    }

    protected function onExecute(): void
    {
        if($this->form !== null){
            $this->executeChildManually($this->form);
        }
        parent::onExecute(); // TODO: Change the autogenerated stub
    }

    public function render(): Renderable|array|null
    {
        $a = $this->getElement();

        //TODO if it is JS action, set as button
        if($this->form !== null){
            // Href Resolver + Post

//            foreach ($a->getChildElementCollection() as $childElement) {
//                $this->formSubmit->append($childElement);
//            }
            $rc = parent::renderChildren();
            $this->formSubmit->append($rc);
            $toRender = $this->form->render();

        }else {

            if($this->linkHook !== null){
                // Will create an anchor for the link hook

                $a->setTag("a");
                $a->addClass("btn");
                $a->setAttribute("role", "button");
                if($this->finalLinkHookMode !== null){
                    switch ($this->finalLinkHookMode) {
                        case LinkHookMode::MODIFY_RECEIVER:
                            $this->linkHook->renderLinkHookIntoElement($this, $a);
                            break;
                        case LinkHookMode::RENDER_JAVASCRIPT:
                            $javascriptCode = $this->linkHook->renderLinkHookIntoJavascript($this, []);
                            $a->setAttribute("onclick", $javascriptCode, false);
                            break;
                    }
                }
                $a->setRequireEndTag(true);

            }else if(is_string($this->pathOrLink)){
                $a->setTag("a");
                $a->addClass("btn");
                $a->setAttribute("role", "button");
                $a->setAttribute("href", $this->pathOrLink);
                $a->setRequireEndTag(true);
            }else if($this->hrefResolver !== null){
                $a->setTag("a");
                $a->addClass("btn");
                $a->setAttribute("role", "button");
                $a->setAttribute("href", $this->hrefResolver->resolve());
                $a->setRequireEndTag(true);
            }else{
                $a->setTag("button");
                $a->addClass("btn");
                $a->setAttribute("type", "button");
                $a->setRequireEndTag(true);
            }

            if($this->disabled){
                $a->addClass("disabled");
                $a->setAttribute("aria-disabled", "true");
            }

            $this->styleButton($a);

            $rc = parent::renderChildren();
            Renderable_Node::renderRenderedTo($rc, $a);

            $toRender = $a;

        }

        return Rendered::of($toRender);

    }

}
