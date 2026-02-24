<?php
namespace monolitum\model\attr;

use monolitum\model\AnonymousModel;
use monolitum\model\AttrExt;
use monolitum\model\ValidatedValue;

interface Attr
{

    public function getId(): string;

    public function getModel(): AnonymousModel;

    public function findExtension(string $class): ?AttrExt;

    /**
     * First validation of the user value received by parameter. Value might be empty, which means that is null.
     * Returns a ValidatedValue that tells if the value was valid or not and the "parsed" value for this attribute.
     */
    public function validate(mixed $value): ValidatedValue;

    /**
     * Converts a valid value into a string
     */
    public function stringValue(mixed $value): string;

}

