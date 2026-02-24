<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\bootstrap\BSPage;
use monolitum\frontend\component\JSInlineScript;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;

class FormControl_Select extends FormControl
{

    private bool $picker = false;

    private bool $searchable = false;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("select"), $builder, "form-select");
    }

    public function setPicker($picker=true): void
    {
        $this->picker = $picker;
    }

    public function setSearchable($searchable=true): void
    {
        $this->searchable = $searchable;
    }

    protected function onBuild(): void
    {

        if($this->picker){
            BSPage::findSelf()->includeBootstrapSelect2IfNot();

            parent::onBuild();

            $this->append((new JSInlineScript())
                ->addScript(
"
$( '#" . $this->getId() . "' ).select2( {
    theme: \"bootstrap-5\",
    width: $( this ).data( 'width' ) ? $( this ).data( 'width' ) : $( this ).hasClass( 'w-100' ) ? '100%' : 'style',
    placeholder: $( this ).data( 'placeholder' ),
" . (
    $this->searchable ? "" : "minimumResultsForSearch: Infinity,"
) . "
    // allowClear: true
} );
"));
        }else{
            parent::onBuild();
        }
    }

    public function render(): Renderable|array|null
    {
        // No children are rendered if it is hidden
        if($this->getElement()->getAttribute("type") !== "hidden"){
            Renderable_Node::renderRenderedTo($this->renderChildren(), $this->getElement());
        }
        return Rendered::of($this->getElement());
    }

}

