<?php

namespace monolitum\frontend\form;

use Closure;
use monolitum\core\Find;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\model\attr\Attr;
use monolitum\model\AttrExt_Validate;

abstract class AbstractHtmlElementNodeFormAttr extends HtmlElementNode implements I_Form_Attr
{
    use Trait_From_Attr;

    public function __construct(HtmlElement|string $element, Attr|string $attr, ?Closure $builder = null)
    {
        parent::__construct($element, $builder);
        $this->attr = $attr;
    }

    protected function onBuild(): void
    {
        $this->form = Find::pushAndGet(Form::class);
        $this->attr = $this->form->_getAttr($this->attr);

        if(!($this->attr instanceof Attr))
            throw new DevPanic("AbstractHtmlElementNodeFormAttr works only with real Attr");

        $this->form->_registerFormAttr($this, $this->attr);
        $this->formExt = $this->attr->findExtension(AttrExt_Form::class);
        $this->validateExt = $this->attr->findExtension(AttrExt_Validate::class);

        parent::onBuild();
    }

}
