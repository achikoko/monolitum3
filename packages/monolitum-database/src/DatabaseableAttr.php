<?php

namespace monolitum\database;

use monolitum\model\attr\Attr;

/**
 * Interface to mark an attribute as storable into a database
 */
interface DatabaseableAttr extends Attr
{
    /**
     * Retrieve the type to append to the name of the attribute in the DDL when creating the table into database.
     */
    function getDDLType(): string;

    function getInsertUpdatePlaceholder(): string;

    function getValueForQuery(mixed $rawValue): mixed;

    function parseValue(mixed $dbValue): mixed;

}
