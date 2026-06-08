<?php

namespace monolitum\model\values;

readonly class File
{

    /**
     * @param string $name Original name of the file
     * @param string $type Mime type of the file
     * @param int $size Size in bytes
     * @param string $path Path to the actual file
     */
    public function __construct(public string $name, public string $type, public int $size, public string $path)
    {
        // TODO rename "tempName" to "location" with a local path in the server
    }


}
