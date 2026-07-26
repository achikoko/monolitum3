<?php

namespace monolitum\quilleditor;

use Closure;
use monolitum\bootstrap\form\BSFormLabel;
use monolitum\core\MObject;
use monolitum\frontend\component\Div;
use monolitum\frontend\form\AbstractRenderableNodeFormAttr;
use monolitum\frontend\form\FormControl_Hidden;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\model\Attr;
use function monolitum\core\m;

class Form_Attr_QuillEditor extends AbstractRenderableNodeFormAttr
{

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $extensions = [];

    private ?int $initialHeight = null;
    private QuillEditor $editor;
    private Div|FormControl_Hidden $component;

    public function __construct(Attr|string $attrId, ?Closure $builder = null)
    {
        parent::__construct($attrId, $builder);
//        $this->experimental_letBuildChildsAfterBuild = true;
    }

    public function setInitialHeight(int $initialHeight): void
    {
        $this->initialHeight = $initialHeight;
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof HtmlElementNodeExtension){
            $this->extensions[] = $object;
            return true;
        }
        return parent::doAcceptChild($object);
    }

    public function getValue(): mixed
    {

        $quillValue = parent::getValue();

        if($quillValue instanceof QuillDocument)
            $quillValue = $quillValue->makeDelta();

        return $quillValue;
    }

    public function onCheckForm(): void
    {
        parent::onCheckForm();

        if($this->hidden){
            $this->component = new FormControl_Hidden(function (FormControl_Hidden $it){
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue())
                    $it->setValue($this->getValue());
            });
        }else{

            $this->component = new Div(function (Div $it){
                $it->addClass("form-group");

                foreach ($this->extensions as $extension) {
                    M($extension);
                }

                $it->append(new BSFormLabel(function(BSFormLabel $it){
                    $it->setFor($this->getFullFieldName());
                    $it->append($this->getLabel());
                }, "form-label"));

                $it->append($this->editor = new QuillEditor(function (QuillEditor $it) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if($this->initialHeight !== null){
                        $it->setInitialHeight($this->initialHeight);
                    }
                    if($this->hasValue())
                        $it->setValue($this->getValue());

                    if($this->getPlaceholder() != null)
                        $it->setPlaceholder($this->getPlaceholder());

                    if($this->isDisabled())
                        $it->setDisabled();

                }));

            });

        }

        $this->append($this->component);

    }

    public function onBeforeValidateForm(): void
    {
        parent::onBeforeValidateForm();

        if($this->component instanceof FormControl_Hidden){
            if($this->hasValue())
                $this->component->setValue($this->getValue());
        }else{

            if($this->hasValue())
                $this->editor->setValue($this->getValue());

            if($this->isDisabled())
                $this->editor->setDisabled();

        }

    }

    public function onNotValidateForm(): void
    {
        parent::onNotValidateForm();

        if($this->component instanceof FormControl_Hidden){
            if($this->hasValue())
                $this->component->setValue($this->getValue());
        }else{

            if($this->hasValue())
                $this->editor->setValue($this->getValue());

            if($this->isDisabled())
                $this->editor->setDisabled();

        }

    }

}
