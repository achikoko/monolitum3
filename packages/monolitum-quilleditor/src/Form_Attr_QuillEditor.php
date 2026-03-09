<?php

namespace monolitum\quilleditor;

use Closure;
use monolitum\bootstrap\form\BSFormLabel;
use monolitum\core\MObject;
use monolitum\frontend\component\Div;
use monolitum\frontend\form\AbstractRenderableNodeFormAttr;
use monolitum\frontend\form\FormControl_Hidden;
use monolitum\frontend\HtmlElementNodeExtension;
use monolitum\model\attr\Attr;
use function monolitum\core\m;

class Form_Attr_QuillEditor extends AbstractRenderableNodeFormAttr
{

    /**
     * @var array<HtmlElementNodeExtension>
     */
    private array $extensions = [];

    private ?int $initialHeight = null;

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

    public function onBeforeBuildForm(): void
    {

        if($this->hidden){
            $component = new FormControl_Hidden(function (FormControl_Hidden $it){
                $it->setId($this->getFullFieldName());
                $it->setName($this->getFullFieldName());
                if($this->hasValue())
                    $it->setValue($this->getValue());
            });
        }else{

            $component = new Div(function (Div $it){
                $it->addClass("form-group");

                foreach ($this->extensions as $extension) {
                    M($extension);
                }

                $it->append(new BSFormLabel(function(BSFormLabel $it){
                    $it->setFor($this->getFullFieldName());
                    $it->setContent($this->getLabel());
                }, "form-label"));

                $it->append(new QuillEditor(function (QuillEditor $it) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if($this->initialHeight !== null){
                        $it->setInitialHeight($this->initialHeight);
                    }
                    if($this->hasValue())
                        $it->setValue($this->getValue());

                    if($this->getPlaceholder() != null)
                        $it->setPlaceholder($this->getPlaceholder());

                    if($this->disabled !== null ? $this->disabled : $this->getForm()->isDisabled())
                        $it->setDisabled();

                }));

            });

        }

        $this->append($component);

    }

}
