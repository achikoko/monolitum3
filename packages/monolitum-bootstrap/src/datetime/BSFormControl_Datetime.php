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
        $simpleLocale = substr($locale, 0, strpos($locale, "_"));

        $page->includeFlatpickrIfNot($simpleLocale);

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
        " . ($this->value !== null ? "'{$this->value}'" : "null") . ",
        '" . (str_replace("_", "-", $locale ?? "")) . "',
        '" . $simpleLocale . "'
    );" .
//    const pickedDate = " . ($this->value ? "new tempusDominus.DateTime(new Date(\"{$this->value}\"))" : "null") . ";
//    const picker = new tempusDominus.TempusDominus(document.getElementById('{$this->getId()}'), {"
//        . ($this->value ? "defaultDate: pickedDate," : "")
//        . ($this->onlyDate ? "display: {
//            viewMode: 'calendar',
//            components: {
//              clock: false,
//              hours: false,
//              minutes: false,
//              seconds: false,
//              useTwentyfourHour: undefined
//            },
//          },
//          localization: {
//            dayViewHeaderFormat: { month: 'long', year: 'numeric' },
//                startOfTheWeek: 1,
//            locale: '" . (str_replace("_", "-", TSLang::pushAndGetLang() ?? "")) . "',
//            format: 'L'
//          }," : "display: {
////            viewMode: 'calendar',
////            components: {
////              clock: false,
////              hours: false,
////              minutes: false,
////              seconds: false,
////              useTwentyfourHour: undefined
////            },
//          },
//          localization: {
//            dayViewHeaderFormat: { month: 'long', year: 'numeric' },
//            startOfTheWeek: 1,
//            locale: '" . (str_replace("_", "-", TSLang::pushAndGetLang() ?? "")) . "',
//            format: 'LLLL'
//          },")
//    . "});
//    // 2. Overwrite the format function (handles how dates display in the input)
//    picker.dates.formatInput = function(date) {
//        if (!date) return '';
//        // Format manually or use a library like dayjs/moment
//        let options = {
////          weekday: 'long',
//          year: 'numeric',
//          month: 'long',
//          day: 'numeric',
//        };
//        return new Intl.DateTimeFormat(\"" . (str_replace("_", "-", TSLang::pushAndGetLang() ?? "")) . "\", options).format(date);
//    };
//
//    // FORCE THE PICKER TO DISPLAY VALUE VIA YOUR NEW FUNCTION
////    console.log(pickedDate);
//    picker.dates.setValue(pickedDate);
//    picker.dates.setValue(pickedDate);
////    console.log(picker.dates.lastPickedDate + 'ola');
////    picker.dates.setValue(picker.dates.lastPickedDate);
"})();
"
        ));

    }

    public function render(): Renderable|array|null
    {
        // No childs are rendered if it is hidden
        if($this->getElement()->getAttribute("type") !== "hidden"){
            Renderable_Node::renderRenderedTo($this->renderChildren(), $this->getElement());
        }
        return Rendered::of($this->getElement());
    }

}

