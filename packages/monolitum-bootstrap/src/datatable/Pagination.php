<?php

namespace monolitum\bootstrap\datatable;

use monolitum\backend\params\Link;
use monolitum\backend\params\Path;
use monolitum\bootstrap\form\BSFormInputGroup;
use monolitum\bootstrap\form\BSFormSubmit;
use monolitum\bootstrap\layout\EBSFlex;
use monolitum\bootstrap\layout\EBSInlineFlex;
use monolitum\bootstrap\style\BSDisplay;
use monolitum\bootstrap\style\BSMargin;
use monolitum\bootstrap\style\BSPadding;
use monolitum\bootstrap\style\BSText;
use monolitum\bootstrap\values\BSColor;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\component\A;
use monolitum\frontend\component\Div;
use monolitum\frontend\component\Li;
use monolitum\frontend\css\CSSSize;
use monolitum\frontend\form\Form;
use monolitum\frontend\form\FormControl_Select;
use monolitum\frontend\form\FormControl_Select_Option;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\i18n\TS;
use function monolitum\core\m;

class Pagination extends HtmlElementNode
{

    private string|TS|null $prevText = null;

    private string|TS|null $nextText = null;

    private string|TS|null $firstText = null;

    private string|TS|null $lastText = null;

    private int $maxDisplayedPages = 5;

    private int $page;

    private int $items_per_page;

    private int $total;

    private string $param_page;

    private string|TS|null $comboboxButtonText = null;

    private int $max_pages;

    public function __construct($builder)
    {
        parent::__construct(new HtmlElement("div"), $builder);
    }

    public static function correctPageNumber($page, $items_per_page, $total)
    {

        $max_pages = intval($total/$items_per_page);
        if($total % $items_per_page > 0)
            $max_pages++;

        if($max_pages === 0)
            return 1;

        if($page > $max_pages)
            $page = $max_pages;
        else if($page < 1)
            $page = 1;

        return $page;

    }

    public function nextText(string|TS $nextText): self
    {
        $this->nextText = $nextText;
        return $this;
    }

    public function prevText(string|TS $prevText): self
    {
        $this->prevText = $prevText;
        return $this;
    }

    public function firstText(string|TS $firstText): self
    {
        $this->firstText = $firstText;
        return $this;
    }

    public function lastText(string|TS $lastText): self
    {
        $this->lastText = $lastText;
        return $this;
    }

    public function setValues(int $page, int $items_per_page, int $total): self
    {
        $this->page = $page;
        $this->items_per_page = $items_per_page;
        $this->total = $total;
        return $this;
    }

    public function setParam(string $page): self
    {
        $this->param_page = $page;
        return $this;
    }

    public function enableCombobox(string|TS $string): self
    {
        $this->comboboxButtonText = $string;
        return $this;
    }

    protected function onAfterBuild(): void
    {
        // check values
        if($this->items_per_page <= 0)
            $this->items_per_page = 10;

        $max_pages = intval($this->total/$this->items_per_page);
        if($this->total % $this->items_per_page > 0)
            $max_pages++;
        $this->max_pages = $max_pages;

        if($this->page >= $max_pages)
            $this->page = $max_pages;
        else if($this->page < 1)
            $this->page = 1;

        $hasFirst = $this->page > 1;
        $hasPrevious = $this->page > 2;

        $hasNext = $this->page < $max_pages-1;
        $hasLast = $this->page < $max_pages;

        $ul = new HtmlElementNode(new HtmlElement("ul"));
        $ul->addClass("pagination");
        $ul->receive(BSDisplay::inline_flex());

        if($hasFirst){
            $ul->receive($this->makeItem(
                $this->firstText !== null ? $this->firstText : "<<",
                Link::from(Path::fromRelative())
                    ->setCopyAllParams()
                    ->addParams([
                        $this->param_page => 0
                    ])
            ));
        }

        if($hasPrevious){
            $ul->receive($this->makeItem(
                $this->prevText !== null ? $this->prevText : "<",
                Link::from(Path::fromRelative())
                    ->setCopyAllParams()
                    ->addParams([
                        $this->param_page => $this->page-1
                    ])
            ));
        }

        // pages
        $nPages = min($max_pages, $this->maxDisplayedPages);

        $halfPages = intval($nPages/2);

        if($this->page <= $halfPages){
            $first = 1;
            $last = $first + $nPages - 1;
        }else if($this->page >= $max_pages-($halfPages)){
            $last = $max_pages;
            $first = $last - $nPages + 1;
        }else{
            $first = $this->page - ($halfPages);
            $last = $first + $nPages - 1;
        }

        for($i = $first; $i <= $last; $i++){
            $ul->receive($this->makeItem(
                strval($i),
                Link::from(Path::fromRelative())
                    ->setCopyAllParams()
                    ->addParams([
                        $this->param_page => $i
                    ]),
                $this->page === $i
            ));
        }

        if($hasNext){
            $ul->receive($this->makeItem(
                $this->nextText !== null ? $this->nextText : ">",
                Link::from(Path::fromRelative())
                    ->setCopyAllParams()
                    ->addParams([
                        $this->param_page => $this->page+1
                    ])
            ));
        }

        if($hasLast){
            $ul->receive($this->makeItem(
                $this->lastText !== null ? $this->lastText : ">>",
                Link::from(Path::fromRelative())
                    ->setCopyAllParams()
                    ->addParams([
                        $this->param_page => $max_pages
                    ])
            ));
        }

        $this->append($ul);

        if($this->comboboxButtonText !== null)
            $this->append($this->makeCombo());

        parent::onAfterBuild();
    }

    function onNotReceived()
    {
        throw new DevPanic();
    }

    private function makeItem(HtmlElementNode|string|TS $param, Link $link, bool $isActive = false): Li
    {
        $li = new Li(function(Li $it){
            $it->addClass("page-item");
        });

        $a = new A(function(A $it) use ($link, $param, $isActive) {
            BSText::of()->textNoWrap()->pushSelf();
            $it->addClass("page-link");
            if($isActive)
                $it->addClass("active");
            $it->append($param);
            $it->setHref($link);

        });
        $li->append($a);

        return $li;
    }

    private function makeCombo(): EBSInlineFlex
    {

        return new EBSInlineFlex(function (EBSInlineFlex $it) {
            BSMargin::bottom(2)->pushSelf();

            M(Form::fromAnonymousModel(function (Form $it) {

                $it->setMethodGET();
                $it->setDefaultValue($this->param_page, $this->page);
                $it->setLink(Link::from(Path::fromRelative())->setCopyParamsExcept($this->param_page));

                M(new Div(function (Div $it) {

                    M(new BSFormInputGroup());
                    BSPadding::left(2)->pushSelf();

                    M(new FormControl_Select(function (FormControl_Select $it) {
                        M(BSPadding::left(2));

                        $it->style()->width(CSSSize::px(80));

                        $it->setName($this->param_page);

                        for ($i = 1; $i <= $this->max_pages; $i++) {

                            M(new FormControl_Select_Option(strval($i), strval($i), function (FormControl_Select_Option $it) use ($i) {
                                if ($this->page === $i)
                                    $it->setSelected();
                            }));

                        }

                    }));

                    M(new BSFormSubmit(function (BSFormSubmit $it){
                        $it->color(BSColor::primary());
                        $it->setContent($this->comboboxButtonText);
                    }));

                }));

            }));

        });
    }

}
