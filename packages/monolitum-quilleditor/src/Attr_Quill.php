<?php

namespace monolitum\quilleditor;

use monolitum\database\DatabaseableAttr;
use monolitum\model\attr\AbstractAttr;
use monolitum\model\ValidatedValue;
use nadar\quill\Lexer;

class Attr_Quill extends AbstractAttr implements DatabaseableAttr
{

    private function tryToParseValue(?string $value): QuillDocument
    {
        $lexer = new Lexer($value);

        // We'll check if this method fails
        $rendered = $lexer->render();

        return new QuillDocument($lexer, $rendered);
    }


    private function createValueFromRaw(mixed $dbValue): QuillDocument
    {
        $delta = is_string($dbValue) ? [
            "ops" => [
                [
                    "insert" => $dbValue
                ]
            ]
        ] : [
            "ops" => []
        ];

        $lexer = new Lexer($delta);

        // We'll check if this method fails
        $rendered = $lexer->render();

        return new QuillDocument($lexer, $rendered);

    }

    public function validate(mixed $value): ValidatedValue
    {
        if(is_string($value)){

            $trimmedValue = trim($value);

            if($trimmedValue == "")
                return new ValidatedValue(true, true, null, null, $trimmedValue);

            if(PHP_MAJOR_VERSION >= 7){
                try{
                    $quill = $this->tryToParseValue($trimmedValue);
                    return new ValidatedValue(true, true, $quill, null, $trimmedValue);
                }catch (\Error $exception){
                    // Error
                }
            }else{

                try{
                    $quill = $this->tryToParseValue($trimmedValue);
                    return new ValidatedValue(true, true, $quill, null, $trimmedValue);
                }catch (\Exception $exception){
                    // PHP <7 has no Error, catch exception
                }

            }
        }

        return new ValidatedValue(false);
    }

    function getDDLType(): string
    {
        // Quill is stored in a LONGTEXT type, because might be a large json with embedded images.
        return "LONGTEXT";
    }

    function getInsertUpdatePlaceholder(): string
    {
        return "?";
    }

    /**
     * @param $rawValue QuillDocument
     * @return string
     */
    function getValueForQuery($rawValue): ?string
    {
        return $rawValue?->makeDelta();
    }

    function parseValue($dbValue): ?QuillDocument
    {
        if($dbValue != null){ // Simple !=

            if(PHP_MAJOR_VERSION >= 7){
                try{
                    return $this->tryToParseValue($dbValue);
                }catch (\Error $exception){
                    return $this->createValueFromRaw($dbValue);
                }
            }else{

                try{
                    return $this->tryToParseValue($dbValue);
                }catch (\Exception $exception){
                    return $this->createValueFromRaw($dbValue);
                }

            }
        }

        return null;
    }

    public function stringValue($value): string
    {
        if($value instanceof QuillDocument) {
            return $value->makeDelta();
        }
        return "";
    }
}
