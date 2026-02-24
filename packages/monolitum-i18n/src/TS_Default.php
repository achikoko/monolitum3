<?php

namespace monolitum\i18n;

/**
 * Translatable string
 */
class TS_Default extends TS
{

    private string|null $defaultString = null;

    private array $stringsByLanguage = [];

    public function getTranslation(?string $lang, array $params = null): ?string
    {
        if($lang === null){
            if($this->defaultString !== null){
                return $this->defaultString;
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    return $value;
                }
                return null;
            }
        }else{
            if(array_key_exists($lang, $this->stringsByLanguage)){
                return $this->stringsByLanguage[$lang];
            }else{
                return $this->getTranslation(null);
            }
        }
    }

//    public function add($lang, $string)
//    {
//        if($lang === null){
//            $this->defaultString = $string;
//        }else{
//            $this->stringsByLanguage[$lang] = $string;
//        }
//    }

    /**
     * @param string[] $string
     * @return TS_Default
     */
    public static function ofStringArray(array $string): TS_Default
    {
        $ts = new TS_Default();
        foreach ($string as $lang => $value){
            if($lang === null){
                $ts->defaultString = $value;
            }else{
                $ts->stringsByLanguage[$lang] = $value;
            }
        }
        return $ts;
    }

}
