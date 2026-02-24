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

    /**
     * @var Form
     */
    protected Form $form;

    /**
     * @var Attr
     */
    protected string|Attr $attr;

    protected ?AttrExt_Form $formExt = null;

    /**
     * @var AttrExt_Validate
     */
    protected AttrExt_Validate $validateExt;

    /**
     * @var string
     */
    protected string $label;

    /**
     * @var string|TS
     */
    private TS|string $placeholder;

    /**
     * @var bool
     */
    protected ?bool $disabled = null;

    /**
     * @var bool
     */
    protected ?bool $hidden = null;

    /**
     * @var bool
     */
    private bool $userSetInvalid = false;

    /**
     * @var string|TS|HtmlElementNode}
     */
    protected $invalidText;

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

    /**
     * @param bool $disabled
     * @return $this
     */
    public function disabled(bool $disabled = true): void
    {
        $this->disabled = $disabled;
    }

    /**
     * @param bool|null $hidden
     * @return $this
     */
    public function hidden(?bool $hidden=true): void
    {
        $this->hidden = $hidden;
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

    /**
     * @param string|HtmlElementNode|TS|null $string
     * @return $this
     */
    public function setInvalid(HtmlElementNode|string|TS $string=null): static
    {
        $this->userSetInvalid = true;
        $this->invalidText = $string;
        return $this;
    }

    /**
     * @param string|TS $label
     */
    public function label(string|TS $label): void
    {
        $this->label = $label;
    }

    /**
     * @param string|TS $placeholder
     */
    public function placeholder(string|TS $placeholder): void
    {
        $this->placeholder = $placeholder;
    }

    /**
     * @return string
     */
    protected function getFullFieldName()
    {
        return $this->form->_getFullFieldName($this->attr);
    }

    /**
     * @return string|TS
     */
    protected function getLabel(): string|TS|null
    {
        $label = $this->formExt?->getLabel();
        if($label == null)
            $label = $this->label;
        return $label;
    }

    /**
     * @return string
     */
    public function getPlaceholder(): string|TS
    {
        return $this->placeholder;
    }
    /**
     * Returns if the value that user set is invalid.
     * @return bool|null
     */
    protected function isValid(): ?bool
    {
        if($this->form->isSilentValidation())
            return null;
        $isValid = $this->form->getValidatedValue($this->attr);
        if($isValid === null)
            return null;
        return $isValid->isValid() && !$this->userSetInvalid;
    }

    /**
     * Tells if there is a value ready to be displayed to the user.
     * @return bool
     */
    public function hasValue(): bool
    {
        return $this->form->getDisplayValue($this->attr)->isWellFormat();
    }

    /**
     * @return mixed|null
     */
    public function getValue(): mixed
    {
        return $this->form->getDisplayValue($this->attr)->getValue();
    }

    /**
     * @return Attr
     */
    public function getAttr(): Attr|string
    {
        return $this->attr;
    }

    /**
     * @return AttrExt_Form
     */
    public function getFormExt(): ?AttrExt_Form
    {
        return $this->formExt;
    }

    /**
     * @return AttrExt_Validate
     */
    public function getValidateExt(): AttrExt_Validate
    {
        return $this->validateExt;
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        return $this->form;
    }

}
