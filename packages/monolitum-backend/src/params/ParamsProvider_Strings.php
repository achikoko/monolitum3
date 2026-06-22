<?php

namespace monolitum\backend\params;

interface ParamsProvider_Strings extends ParamsProvider
{

    function retrieveParam(string $param): ?string;

    /**
     * Retrieve several (string) values from the parameters. And drop them into the provided array.
     * @param array<string, ?string> $returnArray Array to fill with the parameters.
     * @param null|string[] $paramsSelection Selected parameters, if null, then the all are retrieved with exceptions.
     * @param string[] $exceptions Only if all parameters selected, these paremters are excepted.
     * @return void
     */
    function retrieveParams(array &$returnArray, array|null $paramsSelection, array $exceptions): void;

}
