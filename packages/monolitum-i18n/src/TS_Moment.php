<?php

namespace monolitum\i18n;

use Moment\CustomFormats\MomentJs;
use Moment\Moment;

/**
 * Translatable string
 */
class TS_Moment extends TS
{
    /** @var MomentJs[] */
    private static array $momentsByLanguage = [];

    public static function addMoment(string $lang, MomentJs $moment)
    {
        TS_Moment::$momentsByLanguage[$lang] = $moment;
    }

    private Moment $moment;
    private string $format;

    public static function format(Moment $moment, string $format, string $lang)
    {
        if(array_key_exists($lang, TS_Moment::$momentsByLanguage)){
            return $moment->format($format, TS_Moment::$momentsByLanguage[$lang]);
        }else{
            return $moment->format($format);
        }
    }

    public function getTranslation(?string $lang, array $params = null): ?string
    {
        if($lang === null){
            return $this->moment->format($this->format);
        }else{

            if(array_key_exists($lang, TS_Moment::$momentsByLanguage)){
                return $this->moment->format($this->format, TS_Moment::$momentsByLanguage[$lang]);
            }else{
                return $this->getTranslation(null);
            }
        }
    }

    public static function newFromMoment(Moment $moment, string $format): TS_Moment
    {
        $ts = new TS_Moment();
        $ts->moment = $moment;
        $ts->format = $format;
        return $ts;
    }

    public static function newFromDateTime(\DateTime $dateTime, string $format): TS_Moment
    {
        $ts = new TS_Moment();
        $ts->moment = Moment::fromDateTime($dateTime);
        $ts->format = $format;
        return $ts;
    }

}
