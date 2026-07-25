<?php
namespace monolitum\frontend\css;

class CSSSize implements SizeAutoProperty
{

    private string|int|float|null $number;

    /**
     * @var CSSUnit
     */
    private CSSUnit $unit;

    /**
     * @param float|int|string $number
     * @param CSSUnit $unit
     */
    public function __construct(float|int|string|null $number, CSSUnit $unit)
    {
        $this->number = $number;
        $this->unit = $unit;
    }

    public static function px($number): CSSSize
    {
        return new CSSSize($number, CSSUnit::px());
    }

    public static function pct($number): CSSSize
    {
        return new CSSSize($number, CSSUnit::pct());
    }

    public static function auto(): CSSSize
    {
        return new CSSSize(null, CSSUnit::auto());
    }

    function write(): string
    {
        if($this->number !== null){
            return $this->number . $this->unit->write();
        }else{
            return $this->unit->write();
        }

    }
}
