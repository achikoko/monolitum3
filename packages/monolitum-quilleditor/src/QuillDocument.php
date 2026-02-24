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

}
