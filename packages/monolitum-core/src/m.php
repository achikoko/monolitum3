<?php

namespace monolitum\core;

/** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
function m(MObject $object, MNode $from  = null): mixed
{
    if($from === null)
        Monolitum::getInstance()->push($object);
    else
        Monolitum::getInstance()->pushFrom($object, $from);
    return $object;
}

//class M
//{
//    /** @noinspection PhpMixedReturnTypeCanBeReducedInspection */
//    public static function push(MObject $object, MNode $from  = null): mixed
//    {
//        if($from === null)
//            Monolitum::getInstance()->push($object);
//        else
//            Monolitum::getInstance()->pushFrom($object, $from);
//        return $object;
//    }
//}
