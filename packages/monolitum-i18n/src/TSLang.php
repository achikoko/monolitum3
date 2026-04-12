<?php

namespace monolitum\i18n;

use monolitum\core\Find;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

readonly class TSLang implements MObject
{

    public function __construct(public string $locale)
    {

    }

    public static function pushAndGetLangWithOverwritten(?string $overwritten=null): ?string
    {
        if($overwritten !== null){
            return $overwritten;
        }else{
            return self::pushAndGetLang();
        }
    }

    public static function pushAndGetLang(): ?string
    {
        /** @var TSLang $tstrlang */
        $tstrlang = Find::pushAndGet(TSLang::class, true, true);
        return $tstrlang?->locale;
    }

    /**
     * @param string $locale
     * @return TSLang
     */
    public static function of(string $locale): TSLang
    {
        return new TSLang($locale);
    }

    function onNotReceived()
    {
        throw new DevPanic("TSLang was not found.");
    }
}
