<?php

namespace monolitum\frontend\form;

use Closure;
use monolitum\core\Find;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\frontend\Renderable_Node;
use monolitum\i18n\TS;
use monolitum\model\attr\Attr;
use monolitum\model\AttrExt_Validate;

abstract class AbstractFormAttr extends Renderable_Node implements I_Form_Attr
{
    use Trait_From_Attr;

    /**
     * @var array<HtmlElementNodeExtension>
     */
    protected array $catchedExtensions = [];

    /**
     * @param HtmlElement $element
     * @param Attr|string $attrId
     * @param callable|null $builder
     */
    public function __construct(Attr|string $attrId, ?Closure $builder = null)
    {
        parent::__construct($builder);
        $this->attr = $attrId;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof HtmlElementNodeExtension){
            $this->catchedExtensions[] = $object;
            return true;
        }
        return parent::doAcceptChild($object);
    }

    public function getCatchedExtensions(): array
    {
        return $this->catchedExtensions;
    }

    protected function onBuild(): void
    {
        $this->form = Find::pushAndGet(Form::class);
        $this->attr = $this->form->_getAttr($this->attr);

        if(!($this->attr instanceof Attr))
            throw new DevPanic("Form_Attr_ElementComponent works only with real Attr");

        $this->form->_registerFormAttr($this, $this->attr);
        $this->formExt = $this->attr->findExtension(AttrExt_Form::class);
        $this->validateExt = $this->attr->findExtension(AttrExt_Validate::class);

        parent::onBuild();
    }

}
