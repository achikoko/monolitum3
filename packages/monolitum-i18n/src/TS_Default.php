<?php

namespace monolitum\i18n;

use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;
use monolitum\frontend\wikimark\WK;

/**
 * Translatable string
 */
class TS_Default extends TS
{

    private string|TS|null $defaultString = null;

    private array $stringsByLanguage = [];

    public function getTranslation(?string $locale, array $params = null): ?string
    {
        if($locale === null){
            if($this->defaultString !== null){
                if($this->defaultString instanceof TS){
                    return $this->defaultString->getTranslation($locale, $params);
                }
                return $this->defaultString;
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    if($value instanceof TS){
                        return $value->getTranslation($locale, $params);
                    }
                    return $value;
                }
                return null;
            }
        }else{
            if(array_key_exists($locale, $this->stringsByLanguage)){
                $selected = $this->stringsByLanguage[$locale];
                if($selected instanceof TS){
                    return $selected->getTranslation($locale, $params);
                }
                return $selected;
            }else{
                return $this->getTranslation(null, $params);
            }
        }
    }

    public function getRenderable(?string $locale, ?array $params = null): ?Renderable
    {
        if($locale === null){
            if($this->defaultString !== null){
                if($this->defaultString instanceof TS){
                    return $this->defaultString->getRenderable($locale, $params);
                }else if(is_string($this->defaultString)){
                    return new HtmlElementContent($this->defaultString);
                }else{
                    return null;
                }
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    if($value instanceof TS){
                        return $value->getRenderable($locale, $params);
                    }else if(is_string($value)){
                        return new HtmlElementContent($value);
                    }
                    return null;
                }
                return null;
            }
        }else{
            if(array_key_exists($locale, $this->stringsByLanguage)){
                $selected = $this->stringsByLanguage[$locale];
                if($selected instanceof TS){
                    return $selected->getRenderable($locale, $params);
                }else if(is_string($selected)){
                    return new HtmlElementContent($selected);
                }
                return null;
            }else{
                return $this->getRenderable(null, $params);
            }
        }
    }

    public function worthRenderAsRenderable(?string $locale, ?array $params = null): bool
    {
        if($locale === null){
            if($this->defaultString !== null){
                if($this->defaultString instanceof TS){
                    return $this->defaultString->worthRenderAsRenderable($locale, $params);
                }else{
                    return false;
                }
            }else{
                foreach ($this->stringsByLanguage as $key => $value){
                    if($value instanceof TS){
                        return $value->worthRenderAsRenderable($locale, $params);
                    }else{
                        return false;
                    }
                }
                return false;
            }
        }else{
            if(array_key_exists($locale, $this->stringsByLanguage)){
                $selected = $this->stringsByLanguage[$locale];
                if($selected instanceof TS){
                    return $selected->worthRenderAsRenderable($locale, $params);
                }
                return false;
            }else{
                return $this->worthRenderAsRenderable(null, $params);
            }
        }
    }

    /**
     * @param string[] $string
     * @return TS_Default
     */
    public static function ofStringArray(array|string $string): TS_Default
    {
        $ts = new TS_Default();
        if(is_string($string)){
            $ts->defaultString = WK::of($string);
        }else {
            foreach ($string as $lang => $value) {
                if ($lang === null) {
                    $ts->defaultString = WK::of($value);
                } else if (is_array($value)) {
                    $props = [];
                    $wkValue = null;
                    foreach ($value as $prop => $propValue) {
                        if (is_string($prop)) {
                            $props[$prop] = $propValue;
                        } else {
                            if ($propValue instanceof TS) {
                                // TODO support concatenating strings
                            } else {
                                $wkValue = WK::of($propValue);
                            }
                        }
                    }

                    if ($wkValue !== null) {
                        // TODO use $props
                        $ts->stringsByLanguage[$lang] = $wkValue;
                    }
                } else {
                    $ts->stringsByLanguage[$lang] = WK::of($value);
                }
            }
        }
        return $ts;
    }

}
