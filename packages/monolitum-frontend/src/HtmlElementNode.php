<?php
namespace monolitum\frontend;

use Closure;
use monolitum\core\MObject;
use monolitum\frontend\css\Style;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\i18n\TS;
use monolitum\i18n\TSLang;

class HtmlElementNode extends Renderable_Node
{

    private readonly HtmlElement $element;

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $extensions = [];

    /**
     * @var array<string, string>
     */
    private ?array $classKeys = null;

    public function __construct(HtmlElement|string $element, ?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->element = $element instanceof HtmlElement ? $element : new HtmlElement($element);
    }

    /**
     * set attribute to html element
     */
    public function setAttribute(string $key, string $value = null, bool $filter = true): self
    {
        $this->element->setAttribute($key, $value, $filter);
        return $this;
    }

    /**
     * @param string $id         html element attribute id
     * @return      $this
     */
    public function setId(string $id): self
    {
        $this->element->setId($id);
        return $this;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->element->getClasses();
    }

    /**
     * @param string $classes
     * @return $this
     */
    public function addClass(...$classes): self
    {
        $this->element->addClass(...$classes);
        return $this;
    }

    /**
     * Sets a class with a key, if this class is reset with the same key, the previous class is removed.
     * @param string $key
     * @param string|null $class
     * @return $this
     */
    public function setClass(string $key, string $class = null): self
    {
        if($this->classKeys !== null){
            if(array_key_exists($key, $this->classKeys)){
                $this->element->removeClass($this->classKeys[$key]);
                if($class !== null){
                    $this->element->addClass($class);
                    $this->classKeys[$key] = $class;
                }else{
                    unset($this->classKeys);
                }
            }else if($class !== null){
                $this->element->addClass($class);
                $this->classKeys[$key] = $class;
            }
        }else if($class !== null){
            $this->classKeys = array($key => $class);
            $this->element->addClass($class);
        }
        return $this;
    }

    /**
     * @return Style
     */
    public function style(): Style
    {
        return $this->element->style();
    }

    /**
     * @param TS|string $content
     * @param bool $raw
     * @return $this
     *
     * @deprecated Use $this->append() instead.
     */
    #[\Deprecated]
    public function setContent(TS|string $content, bool $raw=false): self
    {
        if($raw){
            $this->element->setContent(new HtmlElementContent(TS::unwrapAuto($content), true));
        }else{
            TS::renderAuto($content)?->renderTo($this->element);
        }
        return $this;
    }

    /**
     * @return HtmlElement
     */
    public function getElement(): HtmlElement
    {
        return $this->element;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->element->getId();
    }

    /**
     * @return array
     */
    protected function getExtensions(): array
    {
        return $this->extensions;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof HtmlElementNodeExtension){
            $object->_setElementComponent($this);
            $this->buildAndAppendChild($object);
            $this->extensions[] = $object;
            return true;
        }
        return parent::doAcceptChild($object);
    }

    protected function onAfterBuild(): void
    {
        foreach ($this->extensions as $extension) {
            $extension->apply();
        }
    }

    public function render(): Renderable|array|null
    {
        // This renders the children
        $rendered = parent::render();
        // This applies the children to the element
        Renderable_Node::renderRenderedTo($rendered, $this->element);
        return Rendered::of($this->element);
    }

}

