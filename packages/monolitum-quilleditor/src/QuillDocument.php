<?php

namespace monolitum\quilleditor;

use Closure;
use nadar\quill\Lexer;
use nadar\quill\listener\Align;
use nadar\quill\listener\BackgroundColor;
use nadar\quill\listener\Blockquote;
use nadar\quill\listener\Bold;
use nadar\quill\listener\CodeBlock;
use nadar\quill\listener\Color;
use nadar\quill\listener\Font;
use nadar\quill\listener\Heading;
use nadar\quill\listener\Image;
use nadar\quill\listener\Italic;
use nadar\quill\listener\Link;
use nadar\quill\listener\Lists;
use nadar\quill\listener\Script;
use nadar\quill\listener\Strike;
use nadar\quill\listener\Underline;
use nadar\quill\listener\Video;

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

        $this->lexer = new Lexer($json, false);
        self::processCustomElements($this->lexer);
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

    public function replaceTemplateValues(string $searchPattern, int $captureGroup, Closure|array $function): QuillDocument
    {
        $toReturn = [];
        $json = $this->lexer->getJsonArray();

        foreach ($json as &$jsonValue) {
            if(isset($jsonValue["attributes"])){
                $attributes = &$jsonValue["attributes"];
                if(isset($attributes["link"])){
                    $attributes["link"] = $this->replaceString($searchPattern, $function, $captureGroup, $attributes["link"]);
                }
            }
            if(isset($jsonValue["insert"])){
                $insert = $jsonValue["insert"];
                if(is_string($insert)){
                    $jsonValue["insert"] = $this->replaceString($searchPattern, $function, $captureGroup, $insert);

                }
            }
        }

        $lexer = new Lexer($json, false);
        self::processCustomElements($lexer);

        // We'll check if this method fails
        $rendered = $lexer->render();

        return new QuillDocument($lexer, $rendered);

    }

    public static function tryToParseValue(?string $value): ?QuillDocument
    {
        if($value === null)
            return null;

        $lexer = new Lexer($value, false);
        self::processCustomElements($lexer);

        try{

            // We'll check if this method fails
            $rendered = $lexer->render();
            return new QuillDocument($lexer, $rendered);

        }catch (\Error $exception){
            // Error
            return null;
        }
    }

    private static function processCustomElements(Lexer $lexer): void
    {
        $lexer->registerListener(new Image());
        $lexer->registerListener(new Bold());
        $lexer->registerListener(new Italic());
        $lexer->registerListener(new Color());
        $lexer->registerListener(new BackgroundColor());
        $lexer->registerListener(new Link());
        $lexer->registerListener(new Video());
        $lexer->registerListener(new Strike());
        $lexer->registerListener(new Underline());
        $lexer->registerListener(new Heading());
        $lexer->registerListener(new CodeBlock());

//        $lexer->registerListener(new Text());
        $lexer->registerListener(new TextFromJSQuill());

        $lexer->registerListener(new Lists());
        $lexer->registerListener(new Blockquote());
        $lexer->registerListener(new Font());
        $lexer->registerListener(new Script());
        $lexer->registerListener(new Align());

        $lexer->registerListener(new HorizontalRow());
        $lexer->registerListener(new Size());
    }

    /**
     * @param string $searchPattern
     * @param array|Closure $function
     * @param int $captureGroup
     * @param string $insert
     * @return array|string|string[]|null
     */
    public function replaceString(string $searchPattern, array|Closure $function, int $captureGroup, string $insert): string|array|null
    {
        return preg_replace_callback(
            $searchPattern,
            function ($match) use ($function, $captureGroup) {

                $matchedKey = $match[$captureGroup];

                if (is_array($function)) {
                    $toReplace = $function[$matchedKey] ?? null;
                } else {
                    $toReplace = $function($matchedKey);
                }

                if ($toReplace === null) {
                    return $match[0];
                }

                return $toReplace;
            },
            $insert
        );
    }


}
