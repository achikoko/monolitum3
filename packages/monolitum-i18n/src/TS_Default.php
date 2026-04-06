<?php

namespace monolitum\i18n;

use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;

/**
 * Translatable string
 */
class TS_Default extends TS
{

    private string|TS|null $defaultString = null;

    private array $stringsByLanguage = [];

    public function getTranslation(?string $lang, array $params = null): ?string
    {
        if($lang === null){
            if($this->defaultString !== null){
                if($this->defaultString instanceof TS){
                    return $this->defaultString->getTranslation($lang, $params);
                }
                return $this->defaultString;
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    if($value instanceof TS){
                        return $value->getTranslation($lang, $params);
                    }
                    return $value;
                }
                return null;
            }
        }else{
            if(array_key_exists($lang, $this->stringsByLanguage)){
                $selected = $this->stringsByLanguage[$lang];
                if($selected instanceof TS){
                    return $selected->getTranslation($lang, $params);
                }
                return $selected;
            }else{
                return $this->getTranslation(null, $params);
            }
        }
    }

    public function getRenderable(?string $lang, ?array $params = null): ?Renderable
    {
        if($lang === null){
            if($this->defaultString !== null){
                if($this->defaultString instanceof TS){
                    return $this->defaultString->getRenderable($lang, $params);
                }else if(is_string($this->defaultString)){
                    return new HtmlElementContent($this->defaultString);
                }else{
                    return null;
                }
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    if($value instanceof TS){
                        return $value->getRenderable($lang, $params);
                    }else if(is_string($value)){
                        return new HtmlElementContent($value);
                    }
                    return null;
                }
                return null;
            }
        }else{
            if(array_key_exists($lang, $this->stringsByLanguage)){
                $selected = $this->stringsByLanguage[$lang];
                if($selected instanceof TS){
                    return $selected->getRenderable($lang, $params);
                }else if(is_string($selected)){
                    return new HtmlElementContent($selected);
                }
                return null;
            }else{
                return $this->getRenderable(null, $params);
            }
        }
    }

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
