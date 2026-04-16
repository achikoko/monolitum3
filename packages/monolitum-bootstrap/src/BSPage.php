<?php

namespace monolitum\bootstrap;

use monolitum\backend\params\Path;
use monolitum\bootstrap\style\BSBounds;
use monolitum\bootstrap\values\BSSize;
use monolitum\frontend\component\CSSLink;
use monolitum\frontend\component\JSInlineScript;
use monolitum\frontend\component\JSScript;
use monolitum\frontend\component\Meta;
use monolitum\frontend\HTMLPage;

class BSPage extends HTMLPage{

    public function onBuild(): void
    {
        parent::onBuild();

        Meta::of("viewport", "width=device-width, initial-scale=1.0")->pushSelf();

        BSBounds::of()->height(BSSize::s100())->pushSelf();

        $this->includePopperIfNot();
//        $this->includeBootstrapDatetimeIfNot();

        CSSLink::of(Path::fromRelativeToClass(BSPage::class,"res","bootstrap-reboot.css"))->pushSelf();
        CSSLink::of(Path::fromRelativeToClass(BSPage::class,"res","bootstrap.css"))->pushSelf();
        CSSLink::of(Path::fromRelativeToClass(BSPage::class,"res","sorting-table.css"))->pushSelf();

        JSScript::of(Path::fromRelativeToClass(BSPage::class,"res","bootstrap.js"))->pushSelf();

    }

    public function includeBootstrapDatetimeIfNot(): void
    {
        $this->includeJQueryIfNot();
        if(!$this->getConstant("bootstrap-datetime-js-css")){
            CSSLink::of(Path::fromRelativeToClass(BSPage::class,"res", "bs5-datetime.min.css"))->pushSelf();
            JSScript::of(Path::fromRelativeToClass(BSPage::class,"res", "bs5-datetime.min.js"))->pushSelf();
            $this->setConstant("bootstrap-datetime-js-css");
        }
    }

    public function includeBootstrapSelect2IfNot(): void
    {
        $this->includeJQueryIfNot();
        if(!$this->getConstant("bootstrap-select2-js-css")){
            CSSLink::of(Path::fromRelativeToClass(BSPage::class,"select", "res", "select2.min.css"))->pushSelf();
            CSSLink::of(Path::fromRelativeToClass(BSPage::class,"select", "res", "select2-bootstrap-5-theme.min.css"))->pushSelf();
            CSSLink::of(Path::fromRelativeToClass(BSPage::class,"select", "res", "select2-bootstrap-fixes.css"))->pushSelf();
            JSScript::of(Path::fromRelativeToClass(BSPage::class,"select", "res", "select2.full.min.js"))->pushSelf();
            $this->setConstant("bootstrap-select2-js-css");
        }
    }

    public function includeJQueryIfNot(): void
    {
        if(!$this->getConstant("jquery-js")){
            $this->receive(JSScript::of(Path::fromRelativeToClass(BSPage::class,"res", "jquery-3.7.1.min.js")));
            $this->setConstant("jquery-js");
        }
    }

    public function includePopperIfNot(string... $variants): void
    {
        if(!$this->getConstant("popper-js")){
            $this->receive(JSScript::of(Path::fromRelativeToClass(BSPage::class,"res", "popper.min.js")));
            $this->setConstant("popper-js");
        }

        if ($variants !== null){
            foreach ($variants as $variant){
                if(!$this->getConstant("popper-js-" . $variant)){
                    if($variant === "tooltip"){
                        $this->receive(new JSInlineScript(function (JSInlineScript $it){
                            $it->addScript(
"
(function() {
const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle=\"tooltip\"]')
const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
})();
"
                            );
                        }));
                    }

                    $this->setConstant("popper-js" . $variant);
                }
            }
        }
    }

//    /**
//     * @param HtmlElement $html
//     * @return void
//     */
//    protected function onBeforeEcho(HtmlElement $html): void
//    {
//        parent::onBeforeEcho($html);
////        echo "<!DOCTYPE html>"; // See https://getbootstrap.com/docs/5.3/getting-started/introduction/
//    }

}
