<?php

namespace monolitum\core\util;

use monolitum\database\I_Attr_Databasable;
use monolitum\database\Manager_DB;
use monolitum\entity\attr\Attr;
use monolitum\entity\attr\Attr_Bool;
use monolitum\entity\attr\Attr_Date;
use monolitum\entity\attr\Attr_Decimal;
use monolitum\entity\attr\Attr_Int;
use monolitum\entity\attr\Attr_String;
use monolitum\entity\Entities_Manager;
use monolitum\entity\Entity;
use monolitum\entity\Model;

interface MClosableIterator
{

    public function hasNext(): bool;

    public function nextConsume(): mixed;

    public function firstAndClose(): mixed;

    public function close(): void;

}
