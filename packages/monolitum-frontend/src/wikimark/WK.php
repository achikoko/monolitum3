<?php

namespace monolitum\frontend\wikimark;

use monolitum\frontend\Renderable;
use monolitum\i18n\TS;

class WK extends TS
{
    private function __construct(private readonly string|TS|array $source)
    {

    }

    public function getTranslation(?string $locale, array $params = null): ?string
    {
        if(is_string($this->source)){
            return $this->source;
        }else if(is_array($this->source)){

            if($locale !== null){
                if(array_key_exists($locale, $this->source)){
                    $s = $this->source[$locale];
                    if(is_string($s)){
                        return $s;
                    }
                }
            }

            foreach($this->source as $firstValue){
                $s = $firstValue;
                if(is_string($s)){
                    return $s;
                }
            }

            return null;

        }else{
            return $this->source->getTranslation($locale, $params);
        }
    }

    public function getRenderable(?string $locale, ?array $params = null): ?Renderable
    {
        if(is_string($this->source)){
            $p = new WikiMarkParser();
            return $p->parse($this->source);
        }else if(is_array($this->source)){

            if($locale !== null){
                if(array_key_exists($locale, $this->source)){
                    $s = $this->source[$locale];
                    if(is_string($s)){
                        $p = new WikiMarkParser();
                        return $p->parse($this->source);
                    }
                }
            }

            foreach($this->source as $firstValue){
                $s = $firstValue;
                if(is_string($s)){
                    $p = new WikiMarkParser();
                    return $p->parse($this->source);
                }
            }

            return null;

        }else{
            return $this->source->getRenderable($locale, $params);
        }
    }

    public static function of(string|TS|array $source): WK
    {
        return new WK($source);
    }

}
