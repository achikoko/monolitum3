<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\LinkHook;
use monolitum\frontend\LinkHookMode;

class FormControl_Select_Option extends HtmlElementNode
{

    /**
     * @var array<LinkHook>|null
     */
    private array|null $linkHooks = null;

    public function __construct(string $value=null, ?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("option"), $builder);
        $option = $this->getElement();
        $option->setAttribute("value", $value);
    }

    public function addOnOptionSelectedHook(LinkHook $hook): void
    {
        // TODO
    }

    /**
     * @param $value
     */
    public function setValue(string $value): void
    {
        $option = $this->getElement();
        $option->setAttribute("value", $value);
    }

    /**
     * @param bool $value
     */
    public function setSelected(bool $value = true): void
    {
        $option = $this->getElement();
        if ($value) {
            $option->setAttribute('selected', 'selected');
        } else {
            $option->setAttribute('selected', null);
        }
    }

}

