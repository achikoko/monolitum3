<?php
namespace monolitum\frontend\css;

class CSSUnit
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function px(): CSSUnit
    {
        return new CSSUnit("px");
    }

    public static function pct(): CSSUnit
    {
        return new CSSUnit("%");
    }

    public static function auto(): CSSUnit
    {
        return new CSSUnit("auto");
    }


    public function write(){
        return $this->value;
    }

}
