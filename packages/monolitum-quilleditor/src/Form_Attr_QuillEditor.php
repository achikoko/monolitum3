<?php

namespace monolitum\quilleditor;

use monolitum\bootstrap\form\BSFormLabel;
use monolitum\frontend\component\Div;
use monolitum\frontend\form\AbstractHtmlElementNodeFormAttr;
use monolitum\frontend\form\AbstractRenderableNodeFormAttr;
use monolitum\frontend\form\FormControl_Hidden;
use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;

class Form_Attr_QuillEditor extends AbstractRenderableNodeFormAttr
{

    private HtmlElementNode $component;

    public function __construct($attrId, $builder = null)
    {
        parent::__construct($attrId, $builder);
//        $this->experimental_letBuildChildsAfterBuild = true;
    }

    public function getValue(): mixed
    {

        $quillValue = parent::getValue();

        if($quillValue instanceof QuillDocument)
            $quillValue = $quillValue->makeDelta();

        return $quillValue;
    }

    public function onAfterBuildForm(): void
    {

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

//                $it->push(...$this->getCatchedExtensions());

                $it->append(new BSFormLabel(function(BSFormLabel $it){
                    $it->setFor($this->getFullFieldName());
                    $it->setContent(TS::unwrap($this->getLabel()));
                }, "form-label"));

                $it->append(new QuillEditor(function (QuillEditor $it) {
                    $it->setId($this->getFullFieldName());
                    $it->setName($this->getFullFieldName());
                    if($this->hasValue())
                        $it->setValue($this->getValue());

                    if($this->getPlaceholder() != null)
                        $it->setPlaceholder(TS::unwrap($this->getPlaceholder()));

                    if($this->disabled !== null ? $this->disabled : $this->getForm()->isDisabled())
                        $it->setDisabled();

                }));

            });

        }

        $this->append($this->component);

    }

}
