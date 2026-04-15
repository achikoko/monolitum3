<?php

namespace monolitum\i18n;

use DateTime;
use DateTimeInterface;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;

/**
 * Translatable string
 */
abstract class TS
{

    public static function unwrapAuto(mixed $string, ?string $overwrittenLanguage = null, ?array $params=null): ?string
    {
        return TS::unwrap($string, TSLang::pushAndGetLangWithOverwritten($overwrittenLanguage), $params);
    }

    public static function unwrap(mixed $string, ?string $lang=null, ?array $params=null): ?string
    {
        if(is_string($string)){
            return $string;
        }else if($string instanceof TS){
            return $string->getTranslation($lang, $params);
        }else if(is_array($string)){
            if(array_key_exists($lang, $string)){
                return self::unwrap($string[$lang], $lang, $params);
            }else{
                foreach($string as $firstValue){
                    return self::unwrap($firstValue, $lang, $params);
                }
                return null;
            }
        }else{
            return $string;
        }
    }

    public static function renderAuto(mixed $string, ?string $overwrittenLanguage = null, ?array $params=null): Renderable
    {
        return TS::render($string, TSLang::pushAndGetLangWithOverwritten($overwrittenLanguage), $params) ?? Rendered::ofEmpty();
    }

    public static function render(mixed $string, ?string $lang=null, ?array $params=null): Renderable
    {
        if(is_string($string)){
            return new HtmlElementContent($string);
        }else if($string instanceof TS){
            return $string->getRenderable($lang, $params) ?? Rendered::ofEmpty();
        }else if(is_array($string)){
            if(array_key_exists($lang, $string)){
                return self::render($string[$lang], $lang, $params);
            }else{
                foreach($string as $firstValue){
                    return self::render($firstValue, $lang, $params);
                }
                return Rendered::ofEmpty();
            }
        }else if ($string === null) {
            return Rendered::ofEmpty();
        }else{
            return $string;
        }
    }

    public abstract function getTranslation(?string $locale, ?array $params=null): ?string;

    public abstract function getRenderable(?string $locale, ?array $params=null): ?Renderable;

    /**
     * @param string[] $string
     * @return TS_Default
     */
    public static function from(array|string $string): TS_Default{
        return TS_Default::ofStringArray($string);
    }

//    public static function fromMoment(Moment $moment, string $format): TS_Moment
//    {
//        return TS_Moment::newFromMoment($moment, $format);
//    }
//
//    public static function fromDateTime(\DateTime $moment, string $format): TS_Moment
//    {
//        return TS_Moment::newFromDateTime($moment, $format);
//    }
//
    public static function fromFormat(DateTimeInterface $dateTime, string $format): TS_Moment
    {
        return TS_Moment::newFromDateTime($dateTime, $format);
    }

}
