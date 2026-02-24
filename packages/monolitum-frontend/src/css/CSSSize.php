<?php
namespace monolitum\frontend\css;

class CSSSize implements SizeAutoProperty
{

    /**
     * @var numeric
     */
    private string|int|float $number;

    /**
     * @var CSSUnit
     */
    private CSSUnit $unit;

    /**
     * @param float|int|string $number
     * @param CSSUnit $unit
     */
    public function __construct(float|int|string $number, CSSUnit $unit)
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

    function write(): string
    {
        return $this->number . $this->unit->write();
    }
}
