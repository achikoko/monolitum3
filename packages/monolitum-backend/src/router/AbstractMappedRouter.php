<?php

namespace monolitum\backend\router;

use Closure;
use monolitum\core\MNode;

abstract class AbstractMappedRouter extends MNode
{

    /**
     * @var array<mixed, MNode|Closure>
     */
    protected array $map = [];

    protected MNode|Closure|null $defaultRoute = null;

    function __construct(?Closure $builder = null){
        parent::__construct($builder);
    }

    public function setDefaultRoute(MNode|Closure|null $node): self
    {
        $this->defaultRoute = $node;
        return $this;
    }

}
