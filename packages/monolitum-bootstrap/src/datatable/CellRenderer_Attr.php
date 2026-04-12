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
use monolitum\model\attr\Attr;
use monolitum\model\attr\Attr_Bool;
use monolitum\model\attr\Attr_Date;
use monolitum\model\attr\Attr_DateTime;
use monolitum\model\attr\Attr_Decimal;
use monolitum\model\attr\Attr_Int;
use monolitum\model\attr\Attr_String;
use monolitum\model\AttrExt_Validate_String;
use monolitum\model\Entity;

class CellRenderer_Attr implements CellRenderer
{
    private ?string $format = null;

    public function __construct(private readonly Attr|string $attr, private readonly ?Closure $valueProcessor)
    {

    }

    public function format(string $format): self
    {
        $this->format = $format;
        return $this;
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
            $attr = $entity->getAttr($this->attr);
            if($attr instanceof Attr_String){
                /** @var AttrExt_Validate_String $extValidate */
                $extValidate = $attr->findExtension(AttrExt_Validate_String::class);
                $value = $this->processValue($entity, $entity->getString($attr));
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
                return Text::of(strval($this->processValue($entity, $entity->getInt($attr))));
            }else if($attr instanceof Attr_Decimal){
                return Text::of(strval($this->processValue($entity, $entity->getInt($attr) / pow(10, $attr->getDecimals()))));
            }else if($attr instanceof Attr_Date || $attr instanceof Attr_DateTime){
                $val = $this->processValue($entity, $entity->getDate($attr));
                return Text::of($val !== null ? TS::fromFormat($val, $this->format) : "");
            }else if($attr instanceof Attr_Bool){
                $ch = new FormControl_CheckBox();
                $ch->setDisabled();
                $ch->setValue($this->processValue($entity, $entity->getBool($attr))); // TODO intermediate
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

    public static function of(Attr|string $attr, ?Closure $valueProcessor = null): static
    {
        return new CellRenderer_Attr($attr, $valueProcessor);
    }

}
