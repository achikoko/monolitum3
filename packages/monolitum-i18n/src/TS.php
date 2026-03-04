<?php

namespace monolitum\i18n;

use Moment\Moment;

/**
 * Translatable string
 */
abstract class TS
{

    /**
     * @param mixed $string
     * @return string|null
     */
    public static function unwrapAuto(mixed $string, ?string $overwrittenLanguage = null): ?string
    {
        return TS::unwrap($string, TSLang::pushAndGetLangWithOverwritten($overwrittenLanguage));
    }


    /**
     * @param mixed $string
     * @param string|null $lang
     * @return string|null
     */
    public static function unwrap(mixed $string, string $lang=null): ?string
    {
        if(is_string($string)){
            return $string;
        }else if($string instanceof TS){
            return $string->getTranslation($lang);
        }else if(is_array($string)){
            if(array_key_exists($lang, $string)){
                return self::unwrap($string[$lang], $lang);
            }else{
                foreach($string as $firstValue){
                    return self::unwrap($firstValue, $lang);
                }
                return null;
            }
        }else{
            return $string;
        }
    }

    public abstract function getTranslation(?string $lang, array $params=null): ?string;

//    public abstract function add($lang, $string);

    /**
     * @param string[] $string
     * @return TS
     */
    public static function from(array $string): TS_Default{
        return TS_Default::ofStringArray($string);
    }

    public static function fromMoment(Moment $moment, string $format): TS_Moment
    {
        return TS_Moment::newFromMoment($moment, $format);
    }

    public static function fromDateTime(\DateTime $moment, string $format): TS_Moment
    {
        return TS_Moment::newFromDateTime($moment, $format);
    }

}
