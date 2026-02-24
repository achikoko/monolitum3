<?php
namespace monolitum\frontend\form;

use Closure;
use monolitum\core\GlobalContext;
use monolitum\core\panic\DevPanic;
use monolitum\frontend\html\HtmlElement;

class FormControl_File extends FormControl
{

    /**
     * @param callable|null $builder
     */
    public function __construct(?Closure $builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "file");
    }

    public function convertToHidden(): void
    {
        // TODO Files cannot be hidden!!
        throw new DevPanic("Files cannot be hidden (for now)");
    }

}

