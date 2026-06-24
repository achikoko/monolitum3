<?php


namespace monolitum\frontend;

use monolitum\frontend\html\HtmlElementContent;
use monolitum\i18n\TS;

trait AppendTextTrait
{

    abstract function append(Renderable_Node|Renderable|string|TS|array|null ...$objects);

    public function appendRichText(string|TS|array $text): void
    {
        $this->append(TS::from($text));
    }

    public function appendRawText(string $text): void
    {
        $this->append(new HtmlElementContent($text, true));
    }

}
