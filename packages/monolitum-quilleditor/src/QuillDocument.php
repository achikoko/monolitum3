<?php

namespace monolitum\quilleditor;

use nadar\quill\Lexer;

class QuillDocument
{

    private Lexer $lexer;

    private ?string $rendered;

    function __construct(Lexer $lexer, ?string $rendered)
    {
        $this->lexer = $lexer;
        $this->rendered = $rendered;
    }

    public function makeDelta(): string
    {
        return json_encode($this->lexer->getJsonArray());
    }

    public function renderHTML(): string
    {
        if($this->rendered === null){
            $this->rendered = $this->lexer->render();
        }
        return $this->rendered;
    }

    public function replace(string $search, string $replace): void
    {
        $json = $this->lexer->getJsonArray();

        foreach ($json as &$jsonValue) {
            if(isset($jsonValue["insert"])){
                $insert = $jsonValue["insert"];
                if(is_string($insert)){
                    $insert = str_replace($search, "$replace", $insert);
                    $jsonValue["insert"] = $insert;
                }
            }
        }

        $this->lexer = new Lexer($json);
        $this->rendered = $this->lexer->render();//str_replace($search, "$replace", $this->rendered);
    }

    public function identifyTemplateValues(string $searchPattern, int $captureGroup): array
    {
        $toReturn = [];
        $json = $this->lexer->getJsonArray();

        foreach ($json as $jsonValue) {
            if(isset($jsonValue["insert"])){
                $insert = $jsonValue["insert"];
                if(is_string($insert)){
                    if(preg_match_all($searchPattern, $insert, $matches, PREG_SET_ORDER, 0)){
                        foreach ($matches as $match) {
                            if(!in_array($match[$captureGroup], $toReturn)){
                                $toReturn[] = $match[$captureGroup];
                            }
                        }
                    }
                }
            }
        }

        return $toReturn;
    }

    public static function tryToParseValue(string $value): ?QuillDocument
    {
        $lexer = new Lexer($value);

        try{

            // We'll check if this method fails
            $rendered = $lexer->render();
            return new QuillDocument($lexer, $rendered);

        }catch (\Error $exception){
            // Error
            return null;
        }
    }


}
