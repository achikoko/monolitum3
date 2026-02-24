<?php

namespace monolitum\core\util;

class ListUtils
{

    public static function insertAnElementIntoAnArray(array &$array, mixed $element, ?int $idx = null): void
    {
        if ($idx !== null) {
            if ($array === null) {
                $array = $element;
            } else {
                if (!is_array($array))
                    $array = [$array];
                array_splice($array, $idx, 0, [$element]);
            }
        } else {
            if ($array === null) {
                $array = $element;
            } else if (!is_array($array)) {
                $array = [$array, $element];
            } else {
                $array[] = $element;
            }
        }
    }
}
