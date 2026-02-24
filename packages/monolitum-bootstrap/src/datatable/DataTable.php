<?php

namespace monolitum\bootstrap\datatable;

use Closure;
use monolitum\backend\params\Link;
use monolitum\backend\params\ParamsManager;
use monolitum\backend\params\Path;
use monolitum\backend\resources\HrefResolver;
use monolitum\backend\resources\Request_HrefResolver;
use monolitum\bootstrap\style\BSVerticalAlign;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;
use monolitum\core\util\MClosableIterator;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable;
use monolitum\frontend\Renderable_Node;
use monolitum\frontend\Rendered;
use monolitum\model\attr\Attr;
use monolitum\model\Model;

/**
 * DataTable uses HtmlElementNode to allow adding attributes and classes into the <table> element
 */
class DataTable extends HtmlElementNode
{

    /**
     * @var DataTable_Col[]
     */
    private array $columns = [];

    /**
     * @var HrefResolver[]
     */
    private array $columnHrefResolvers = [];

    private ?Closure $rowRetriever = null;

    /**
     * @var Rendered
     */
    private array $rowComponents = [];

    private Model|string|null $sortable_model = null;

    private Attr|string|null $sortable_attr_sort = null;

    private Attr|string|null $sortable_attr_desc = null;

    private ?Link $sortable_base_link = null;

    private ?DataTable_Col $sortedColumn = null;

    private ?bool $sortedColumnDesc = null;

    private ?bool $responsiveTable = true;

    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("table"), $builder);
        $this->addClass("table");
        $this->receive(BSVerticalAlign::middle());
    }

    /**
     * @param bool $responsiveTable
     */
    public function setResponsiveTable(bool $responsiveTable): void
    {
        $this->responsiveTable = $responsiveTable;
    }

    public function retrieveRows(Closure $rowRetriever): void
    {
        $this->rowRetriever = $rowRetriever;
    }

    public function setSortableParams(Model|string $class, Attr|string $sort, Attr|string $desc=null): void
    {
        $this->sortable_model = $class;
        $this->sortable_attr_sort = $sort;
        $this->sortable_attr_desc = $desc;
    }

    public function setSortableBaseLink(?Link $sortable_base_link): void
    {
        $this->sortable_base_link = $sortable_base_link;
    }

    public function getSortedColumnId(): ?string
    {
        return $this->sortedColumn?->getSortableId();
    }

    public function getSortedColumnDesc(): ?bool
    {
        return $this->sortedColumnDesc;
    }

    private function detectSorting(): void
    {
        if ($this->sortable_model !== null && $this->sortable_attr_sort !== null) {
            // Detect if it is sorted

            $sortValidatedValue = ParamsManager::pushGetParameterValidatedValue($this->sortable_model, $this->sortable_attr_sort);

            $sortedColumnName = null;
            if ($sortValidatedValue->isValid()) {
                $sortedColumnName = $sortValidatedValue->getValue();
            }

            if($sortedColumnName === null)
                return;

            $sortedColumn = null;
            foreach ($this->columns as $column){
                if($column->isSortable()){
                    if($column->getSortableId() === $sortedColumnName) {
                        $sortedColumn = $column;
                        break;
                    }
                }
            }

            if($sortedColumn === null)
                return;

            $descValidatedValue = $this->sortable_attr_desc !== null ? ParamsManager::pushGetParameterValidatedValue($this->sortable_model, $this->sortable_attr_desc) : null;

            $desc = false;
            if ($descValidatedValue->isValid() && !$descValidatedValue->isNull()) {
                $desc = $descValidatedValue->getValue();
            }

            $this->sortedColumn = $sortedColumn;
            $this->sortedColumnDesc = $desc;

        }
    }

    public function doAcceptChild(MObject $object): bool
    {
        if($object instanceof DataTable_Col){
            $this->columns[] = $object;
            return true;
        }else if($object instanceof Renderable_Node){
            throw new DevPanic("DataTable only accepts DataTable_Col children.");
        }
        // We return false to tell the acceptor to forward a not recognized object to the parent.
        // (If we called the parent, it would accept it mistakenly)
        return parent::doAcceptChild($object);
    }

    protected function onAfterBuild(): void
    {
        $this->detectSorting();

        $baseLink = $this->sortable_base_link;
        if($baseLink === null)
            $baseLink = Link::from(Path::fromRelative())->setCopyAllParams();

        foreach ($this->columns as $column){
            $this->buildAndAppendChild($column);
            if($column->isSortable()){

                $myLink = $baseLink->copy();
                $myLink->addParams([
                    $this->sortable_attr_sort => $column->getSortableId()
                ]);

                if($column === $this->sortedColumn && !$this->sortedColumnDesc){
                    $myLink->addParams([
                        $this->sortable_attr_desc => true
                    ]);
                }else if($this->sortable_attr_desc !== null){
                    $myLink->removeParams(
                        $this->sortable_attr_desc
                    );
                }

                $request = new Request_HrefResolver($myLink);
                $this->receive($request);
                $this->columnHrefResolvers[] = $request->getHrefResolver();
            }else{
                $this->columnHrefResolvers[] = null;
            }
        }

        // Build header

        if($this->rowRetriever !== null){

            $callable = $this->rowRetriever;

            /** @var MClosableIterator|array $iterator */
            $iterator = $callable($this);

            if($iterator instanceof MClosableIterator){
                while ($iterator->hasNext()){
                    $entity = $iterator->next();
                    $row = $this->createRow($entity);
                    $this->rowComponents[] = $row;
                }
                $iterator->close();
            }else if(is_array($iterator)){
                foreach ($iterator as $item) {
                    $row = $this->createRow($item);
                    $this->rowComponents[] = $row;
                }
            }

        }

        parent::onAfterBuild();
    }

    public function createRow(mixed $entity): array
    {
        $row = [];

        foreach ($this->columns as $column) {

            $renderer = $column->getRenderer();

            if ($renderer instanceof CellRenderer) {
                $rendered = $renderer->render($entity);
            } else if (is_callable($renderer)) {
                $rendered = $renderer($entity);
            } else {
                $rendered = Rendered::ofEmpty();
            }

            if (is_array($rendered)) {
                foreach ($rendered as $item) {
                    if ($item instanceof Renderable_Node)
                        $this->buildAndAppendChild($item);
                }
                $rendered = Rendered::of($rendered);
            } else {

                if ($rendered instanceof Renderable_Node)
                    $this->buildAndAppendChild($rendered);

            }

            $row[] = $rendered;

        }
        return $row;
    }

    public function render(): Renderable|array|null
    {
        $element = $this->getElement();

        $thead = new HtmlElement("thead");
        $theadrow = new HtmlElement("tr");

        // Render header

        $i = 0;
        foreach($this->columns as $column){

            $th = new HtmlElement("th");
            if ($this->sortable_model !== null
                && $this->sortable_attr_sort !== null
                && $column->isSortable()){

                if($column === $this->sortedColumn){
                    if($this->sortedColumnDesc){
                        $th->addClass("sorting","sorting_desc");
                    }else{
                        $th->addClass("sorting","sorting_asc");
                    }
                }else{
                    $th->addClass("sorting","sorting_asc_disabled","sorting_desc_disabled");
                }

                $a = new HtmlElement("a");
                $a->setContent($column->getName());
                $a->setAttribute("href", $this->columnHrefResolvers[$i]->resolve());

                $th->addChildElement($a);
            }else{
                $th->setContent($column->getName());
            }
            $theadrow->addChildElement($th);

            $i++;
        }

        $thead->addChildElement($theadrow);
        $element->addChildElement($thead);

        $tbody = new HtmlElement("tbody");

        foreach($this->rowComponents as $row){

            $tbodyrow = new HtmlElement("tr");

            foreach ($row as $cell) {

                $td = new HtmlElement("td");
                if(is_string($cell)){
                    $td->setContent($cell);
                }else {
                    Renderable_Node::renderRenderedTo($cell, $td);
                }
                $tbodyrow->addChildElement($td);

            }

            $tbody->addChildElement($tbodyrow);

        }

        $element->addChildElement($tbody);

        if($this->responsiveTable){
            $responsiveDiv = new HtmlElement("div");
            $responsiveDiv->addClass("table-responsive");
            $responsiveDiv->addChildElement($element);
            return Rendered::of($responsiveDiv);
        }else{
            return Rendered::of($element);
        }

    }


}
