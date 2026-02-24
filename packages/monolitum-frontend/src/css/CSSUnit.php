<?php
namespace monolitum\frontend\css;

class CSSUnit
{

    private string $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * @return CSSUnit
     */
    public static function px(){
        return new CSSUnit("px");
    }

    /**
     * @return CSSUnit
     */
    public static function pct(){
        return new CSSUnit("%");
    }


    public function write(){
        return $this->value;
    }

}
