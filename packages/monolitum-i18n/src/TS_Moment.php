<?php

namespace monolitum\i18n;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use DateTime;
use DateTimeInterface;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;

/**
 * Translatable string
 */
class TS_Moment extends TS
{

    private CarbonImmutable $carbon;
    private string $format;

    /**
     * @see https://devhints.io/datetime
     * @param DateTime $moment
     * @param string $format
     * @param string $locale
     * @return string
     */
    public static function format(DateTimeInterface $moment, string $format, string $locale): string
    {
        return Carbon::parse($moment)->locale($locale)->isoFormat($format);
    }

    public function getTranslation(?string $locale, array $params = null): ?string
    {
        if($locale === null){
            return $this->carbon->isoFormat($this->format);
        }else{
            return self::format($this->carbon, $this->format, $locale);
        }
    }

    public function getRenderable(?string $locale, ?array $params = null): ?Renderable
    {
        $s = $this->getTranslation($locale, $params);
        if($s !== null){
            return new HtmlElementContent($s);
        }
        return null;
    }

    public static function newFromDateTime(DateTimeInterface $dateTime, string $format): TS_Moment
    {
        $ts = new TS_Moment();
        $ts->carbon = CarbonImmutable::parse($dateTime);
        $ts->format = $format;
        return $ts;
    }

    public function worthRenderAsRenderable(?string $locale, ?array $params = null): ?bool
    {
        return false;
    }
}
