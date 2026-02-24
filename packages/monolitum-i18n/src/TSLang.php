<?php

namespace monolitum\i18n;

use monolitum\core\Find;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

readonly class TSLang implements MObject
{

    public function __construct(public string $lang)
    {

    }

    public static function findWithOverwritten($overwritten=null)
    {
        if($overwritten !== null){
            return $overwritten;
        }else{
            return self::find();
        }
    }

    public static function find()
    {
        /** @var TSLang $tstrlang */
        $tstrlang = Find::pushAndGet(TSLang::class, true, true);
        return $tstrlang?->lang;
    }

    /**
     * @param string $lang
     * @return TSLang
     */
    public static function of(string $lang)
    {
        return new TSLang($lang);
    }

    function onNotReceived()
    {
        throw new DevPanic("TSLang was not found.");
    }
}
