<?php

namespace monolitum\frontend\wikimark;

use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;

class WikiMarkParser
{
    const regexSpecialChars = "*´_`~^¬|";
    const regexSpecialCharsWithScape = self::regexSpecialChars . "\\\\";

    function hasMarks(string $string): bool
    {
        return preg_match(
            "/(^[" . self::regexSpecialChars . "])"
            . "|[^\\\\][" . self::regexSpecialChars . "]/",
            $string
        );
    }

    public function removeEscapes(string $source): string
    {
        return preg_replace(
            "/\\\\([" . self::regexSpecialCharsWithScape . "])$/",
            '${1}',
            $source
        );
    }

    function parse(string $string): Renderable
    {
        $root = [];
        $stack = [];
        $closingCharacterStack = [];
        $lastCharacter = null;

        $buildingString = "";

        $scapeCharacter = false;
        foreach (mb_str_split($string) as $idx => $char) {
            if($scapeCharacter){
                $scapeCharacter = false;
                $buildingString .= $char;
                continue;
            }

            if($char === $lastCharacter){
                array_pop($closingCharacterStack);
                $lastCharacter = count($closingCharacterStack) > 0 ? $closingCharacterStack[count($closingCharacterStack) - 1] : null;
                $closeElement = array_pop($stack);
                if(strlen($buildingString) > 0) {
                    $closeElement->addContent($buildingString);
                    $buildingString = "";
                    if(count($stack) > 0) {
                        $stack[count($stack) - 1]->addChildElement($closeElement);
                    }else{
                        $root[] = $closeElement;
                    }
                }
                continue;
            }

            $openElement = null;
            switch ($char) {
                case '*':
                    $openElement = new HtmlElement('b');
                    $lastCharacter = "*";
                    break;
                case '´':
                    $openElement = new HtmlElement('i');
                    $lastCharacter = "´";
                    break;
                case '_':
                    $openElement = new HtmlElement('u');
                    $lastCharacter = "_";
                    break;
                case '`':
                    $openElement = new HtmlElement('code');
                    $lastCharacter = "`";
                    break;
                case '~':
                    $openElement = new HtmlElement('s');
                    $lastCharacter = "~";
                    break;
                case '^':
                    $openElement = new HtmlElement('sup');
                    $lastCharacter = "^";
                    break;
                case '¬':
                    $openElement = new HtmlElement('sub');
                    $lastCharacter = "¬";
                    break;
                case '|':
                    $openElement = new HtmlElement('mark');
                    $lastCharacter = "|";
                    break;
                case '\\':
                    $scapeCharacter = true;
                    break;
                default:
                    $buildingString .= $char;
            }

            if($scapeCharacter){
                continue;
            }

            if ($openElement != null){
                if(strlen($buildingString)>1) {
                    if(count($stack) > 0) {
                        $stack[count($stack) - 1]->addContent($buildingString);
                    }else{
                        $root[] = $buildingString;
                    }
                    $buildingString = "";
                }

                $stack[] = $openElement;
                $closingCharacterStack[] = $lastCharacter;
            }

        }

        if(strlen($buildingString) > 0) {
            if(count($stack) > 0) {
                $stack[count($stack) - 1]->addContent($buildingString);
            }else{
                $root[] = $buildingString;
            }
        }

        return Rendered::of($root);
    }

}
