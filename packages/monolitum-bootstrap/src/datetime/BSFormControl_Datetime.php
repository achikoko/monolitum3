<?php

namespace monolitum\bootstrap\datetime;

use Closure;
use monolitum\bootstrap\BSPage;
use monolitum\core\Find;
use monolitum\frontend\component\JSInlineScript;
use monolitum\frontend\form\FormControl;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\i18n\TSLang;

class BSFormControl_Datetime extends FormControl
{

    private ?string $value = null;

    private bool $onlyDate = false;
    private bool $showYearsFirst = false;
    private string $simpleLocale;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
    }

    /**
     * @param bool $onlyDate
     */
    public function setOnlyDate(bool $onlyDate = true): void
    {
        $this->onlyDate = $onlyDate;
    }

    /**
     * @param bool $showYearsFirst
     */
    public function setShowYearsFirst(bool $showYearsFirst = true): void
    {
        $this->showYearsFirst = $showYearsFirst;
    }



    public function setValue(?string $value): void
    {
//        parent::setValue($value);
        $this->value = $value;
    }

    protected function onBuild(): void
    {

        /** @var BSPage $page */
        $page = Find::pushAndGet(BSPage::class);
        $page->includeFlatpickrIfNot();

        /** @var string $locale */
        $locale = TSLang::pushAndGetLang();
        $this->simpleLocale = substr($locale, 0, strpos($locale, "_"));

        $page->includeFlatpickrIfNot($this->simpleLocale);

        parent::onBuild();

        $this->setAttribute("placeholder", $this->onlyDate ? "----/--/--" : "----/--/-- --:--");


        // SOLUTION, two fields, one hidden, update the hidden every time the other updates
        $this->append((new JSInlineScript())->addScript(
"
(function(){
    monolitum_flatpickr(
        '{$this->getId()}',
        " . ($this->onlyDate ? "true" : "false"). ",
        " . ($this->showYearsFirst ? "true" : "false"). ",
        '" . (str_replace("_", "-", $locale ?? "")) . "',
        '" . $this->simpleLocale . "'
    );" .
"})();
"
        ));
//        " . ($this->value !== null ? "'{$this->value}'" : "null") . ",

    }

    public function render(): Renderable|array|null
    {
        if($this->value !== null)
            $this->setAttribute("value", $this->value);

        // No childs are rendered if it is hidden
        if($this->getElement()->getAttribute("type") !== "hidden"){
            Renderable_Node::renderRenderedTo($this->renderChildren(), $this->getElement());
        }
//        $link = new HtmlElement("script");
//        $link->setContent(new HtmlElementContent("
//(function(){
//    monolitum_flatpickr(
//        '{$this->getId()}',
//        " . ($this->onlyDate ? "true" : "false"). ",
//        " . ($this->showYearsFirst ? "true" : "false"). ",
//        " . ($this->value !== null ? "'{$this->value}'" : "null") . ",
//        '" . (str_replace("_", "-", $locale ?? "")) . "',
//        '" . $this->simpleLocale . "'
//    );" .
//"})();
//", true));
        return Rendered::of([$this->getElement()]);
    }

}

