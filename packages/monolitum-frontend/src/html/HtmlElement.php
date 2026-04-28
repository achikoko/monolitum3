<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace monolitum\frontend\html;

use monolitum\frontend\css\Style;
use monolitum\frontend\Renderable;

/**
 * Html Element Class
 *
 * @package    HtmlBuilder
 * @author     Sven Sanzenbacher
 */
class HtmlElement implements Renderable
{

    public function __construct(?string $tag, ?string $content = null)
    {
        $this->tag = $tag;
        $this->setContent($content);
    }

    protected ?string $tag;

    /**
     * @var array<string, string>|null
     */
    protected ?array $attributeMap = null;

    /**
     *
     * @var array<string>
     */
    private array $classes = [];

    /**
     * @var Style|null
     */
    private ?Style $style = null;

//    private $isStyleDirty = false;

    /**
     * @var array<HtmlElement|HtmlElementContent>|null
     */
    protected ?array $childElementCollection = null;

    protected bool $requireEndTag = false;

    /**
     * @access      protected
     * @var         array                   black list of attributes witch are not filtered
     */
    protected array $nonSanitizedAttributes = [];

    /**
     * @return      string                  html element tag
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): void
    {
        $this->tag = $tag;
    }

    public function getId(): ?string
    {
        return $this->getAttribute('id');
    }

    public function setId(string $id): static
    {
        $this->setAttribute('id', $id);
        return $this;
    }

    protected function getAttributeMap(): array
    {
        if (is_null($this->attributeMap)) {
            $this->attributeMap = [];
        }
        return $this->attributeMap;
    }

    public function getAttributes(): array
    {
        return $this->getAttributeMap();
    }

    public function hasAttributes(): bool
    {
        if ($this->attributeMap !== null && count($this->attributeMap) > 0) {
            return true;
        }
        return false;
    }

    public function setAttributes(array $attributes): self
    {
        $this->attributeMap = $attributes;
        return $this;
    }

    public function getAttribute(string $key): ?string
    {
        if ($this->attributeMap != null && array_key_exists($key, $this->attributeMap)){
            return $this->attributeMap[$key];
        }
        return null;
    }

    public function hasAttribute(string $key): bool
    {
        return $this->attributeMap != null && array_key_exists($key, $this->attributeMap);
    }

    /**
     * set attribute to html element
     *
     * @param string $key        html element attribute key
     * @param string $value      html element attribute value
     * @param bool $sanitize     set if this attribute must be filtered
     * @return      HtmlElement
     */
    public function setAttribute(string $key, ?string $value, bool $sanitize = true): self
    {
        if (is_null($value)) {
            if($this->attributeMap !== null){
                if(array_key_exists($key, $this->attributeMap)){
                    unset($this->attributeMap[$key]);
                }
                if (in_array($key, $this->nonSanitizedAttributes) !== false) {
                    unset($this->nonSanitizedAttributes[$key]);
                }
            }
        } else {
            if($this->attributeMap === null)
                $this->attributeMap = [];
            $this->attributeMap[$key] = $value;
            if($sanitize){
                if (in_array($key, $this->nonSanitizedAttributes) !== false) {
                    unset($this->nonSanitizedAttributes[$key]);
                }
            }else{
                if (!in_array($key, $this->nonSanitizedAttributes)) {
                    $this->nonSanitizedAttributes[] = $key;
                }
            }
        }
        return $this;
    }

    public function removeAttribute(string $key): self
    {
        if (!is_null($this->attributeMap)) {
            unset($this->attributeMap[$key]);
        }
        return $this;
    }

    public function addClass(?string ...$classes): self
    {
        foreach ($classes as $class)
            if($class !== null)
                $this->classes[] = $class;
        return $this;
    }

    public function removeClass(?string ...$classes): self
    {
        foreach ($classes as $class){
            if($class !== null) {
                $key = array_search($class, $this->classes);
                if ($key !== false)
                    array_splice($array, $key, 1);
            }
        }
        return $this;
    }

    public function hasClasses(): bool
    {
        return count($this->classes) > 0;
    }

    public function getClasses(): array
    {
        return $this->classes;
    }

    public function hasStyle(): bool
    {
        return $this->style !== null;
    }

    /**
     * @return Style
     */
    public function style(): ?Style
    {
        if($this->style === null)
            $this->style = new Style();
        return $this->style;
    }

    /**
     * @return array<HtmlElement|HtmlElementContent>|null
     */
    public function getChildElementCollection(): ?array
    {
        if (is_null($this->childElementCollection)) {
            $this->childElementCollection = [];
        }
        return $this->childElementCollection;
    }

    /**
     * @return array<HtmlElement|HtmlElementContent>|null
     */
    public function getChildElements(): ?array
    {
        return $this->getChildElementCollection();
    }

    public function hasChildElements(): bool
    {
        if (count($this->getChildElementCollection()) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param array<HtmlElement|HtmlElementContent>|null $elements
     * @return $this
     */
    public function setChildElements(array $elements)
    {
        $this->childElementCollection = $elements;
        return $this;
    }

    public function addChildElement(HtmlElement|HtmlElementContent $element): self
    {
        $this->childElementCollection[] = $element;
        return $this;
    }

    /**
     * set html element content, replace all child elements
     *
     * @param string|HtmlElement|HtmlElementContent|null $content        html element content
     * @return      HtmlElement
     */
    public function setContent(string|HtmlElement|HtmlElementContent|null $content): self
    {
        if (!is_null($content)) {
            if ($content instanceof HtmlElement || $content instanceof HtmlElementContent) {
                $htmlElementObject = $content;
            }else{
                $htmlElementObject = new HtmlElementContent($content);
            }
            $this->childElementCollection = [$htmlElementObject];
        }
        return $this;
    }

    public function getContentAsString(){
        if(count($this->childElementCollection) !== 1)
            return null;
        /** @var HtmlElement|HtmlElementContent $elem */
        $elem = $this->childElementCollection[0];
        if($elem instanceof HtmlElementContent)
            return $elem->content;
        return (new HtmlBuilder())->render($elem);
    }

    /**
     * add html element content
     *
     * @param string|null $content        html element content
     * @return      HtmlElement
     */
    public function addContent(string $content = null): self
    {
        if (!is_null($content)) {
            $htmlElementObject = new HtmlElementContent($content);
            $this->childElementCollection[] = $htmlElementObject;
        }
        return $this;
    }

    /**
     * @param bool $requireEndTag
     * @return $this
     */
    public function setRequireEndTag(bool $requireEndTag = true): static
    {
        $this->requireEndTag = $requireEndTag;
        return $this;
    }

    public function requireEndTag(): bool
    {
        return $this->requireEndTag;
    }

    public function isAttributeNotSanitized($key): bool
    {
        return in_array($key, $this->nonSanitizedAttributes);
    }

    /**
     * @return      string                  html output
     */
    public function __toString()
    {
        $htmlBuilder = new HtmlBuilder();
        return $htmlBuilder->render($this);
    }

    function renderTo(HtmlElement $element): void
    {
        if($element instanceof HtmlElement)
            $element->addChildElement($this);
    }

    function onNotReceived()
    {
        // TODO: Implement onNotReceived() method.
    }
}
