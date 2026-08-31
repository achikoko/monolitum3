<?php

namespace monolitum\bootstrap\datatable;

use Closure;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\Text;
use monolitum\frontend\form\FormControl_CheckBox;
use monolitum\frontend\Reference;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\i18n\TS;
use monolitum\model\Attr;
use monolitum\model\Attr_Bool;
use monolitum\model\Attr_Date;
use monolitum\model\Attr_DateTime;
use monolitum\model\Attr_Decimal;
use monolitum\model\Attr_Int;
use monolitum\model\Attr_String;
use monolitum\model\AttrExt_Validate_String;
use monolitum\model\Entity;

class CellRenderer_Attr implements CellRenderer
{
    private ?string $format = null;

    private ?Closure $valueProcessor = null;
    private ?Closure $valueGetter = null;
    private Attr|string|Closure|null $renderAs = null;

    public function __construct(private readonly Attr|string $attr)
    {

    }

    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @param Closure|null $valueProcessor
     */
    public function setValueProcessor(?Closure $valueProcessor): void
    {
        $this->valueProcessor = $valueProcessor;
    }

    /**
     * @param Closure|null $valueGetter
     */
    public function setValueGetter(?Closure $valueGetter): void
    {
        $this->valueGetter = $valueGetter;
    }

    /**
     * @param Attr|string|null $renderAs
     */
    public function setRenderAs(Attr|string|null $renderAs): void
    {
        $this->renderAs = $renderAs;
    }

    /**
     * @inheritDoc
     */
    function prepare(DataTable $datatable): void
    {
        // TODO: Implement prepare() method.
    }

    /**
     * @inheritDoc
     */
    function render(?Entity $entity): Renderable_Node|Rendered
    {
        if($entity == null){
            return new Reference();
        } else {
            $attr = $this->renderAs($entity);
            if($attr instanceof Attr_String){
                /** @var AttrExt_Validate_String $extValidate */
                $extValidate = $attr->findExtension(AttrExt_Validate_String::class);
                $value = $this->processValue($entity, $this->getValueAsString($entity, $attr));
                if($value === null){
                    return Text::of("");
                }else if($extValidate !== null && $extValidate->hasEnum()){
                    $string = $extValidate->getEnumString($value);
                    $string = TS::renderAuto($string);
                    return Text::of($string);
                }else{
                    return Text::of($value);
                }
            }else if($attr instanceof Attr_Int){
                return Text::of(strval($this->processValue($entity, $this->getValueAsInt($entity, $attr))));
            }else if($attr instanceof Attr_Decimal){
                return Text::of(strval($this->processValue($entity, $this->getValueAsDecimalAsFloat($entity, $attr))));
            }else if($attr instanceof Attr_Date){
                $val = $this->processValue($entity, $this->getValueAsDate($entity, $attr));
                return Text::of($val !== null ? TS::fromFormat($val, $this->format ?? "LL") : "");
            }else if($attr instanceof Attr_DateTime){
                $val = $this->processValue($entity, $this->getValueAsDateTime($entity, $attr));
                return Text::of($val !== null ? TS::fromFormat($val, $this->format ?? "LLLL") : "");
            }else if($attr instanceof Attr_Bool){
                $ch = new FormControl_CheckBox();
                $ch->setDisabled();
                $ch->setValue($this->processValue($entity, $this->getValueAsBool($entity, $attr))); // TODO intermediate
                return $ch;
            }else{
                throw new DevPanic("Not recognized col type");
            }
        }
    }

    private function processValue(Entity $entity, mixed $value)
    {
        if($this->valueProcessor !== null){
            return call_user_func($this->valueProcessor, $entity, $value);
        }
        return $value;
    }

    private function getValueAsString(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getString($attr);
        }
    }

    private function getValueAsInt(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getInt($attr);
        }
    }

    private function getValueAsDecimalAsFloat(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getDecimalAsFloat($attr);
        }
    }

    private function getValueAsDate(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getDate($attr);
        }
    }

    private function getValueAsDateTime(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getDateTime($attr);
        }
    }

    private function getValueAsBool(Entity $entity, Attr $attr)
    {
        if($this->valueGetter !== null){
            return call_user_func($this->valueGetter, $entity);
        }else{
            return $entity->getBool($attr);
        }
    }

    public static function of(Attr|string $attr): static
    {
        return new CellRenderer_Attr($attr);
    }

    /**
     * @param Entity $entity
     * @return Attr
     */
    private function renderAs(Entity $entity): Attr
    {
        if($this->renderAs === null){
            return $entity->getAttr($this->attr);
        } if(is_string($this->renderAs)){
            return $entity->getAttr($this->renderAs);
        } else if(is_callable($this->renderAs)){
            return call_user_func($this->valueGetter, $entity);
        } else{
            return $this->renderAs;
        }
    }

}
