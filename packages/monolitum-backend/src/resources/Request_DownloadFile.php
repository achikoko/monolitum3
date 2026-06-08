<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;
use monolitum\core\MObject;
use monolitum\core\panic\DevPanic;

/**
 * "Carta blanca" for any file in the project. Could be a security issue?
 */
class Request_DownloadFile implements MObject
{

    /**
     * @param Path $filePath the file path to be downloaded (it should be relative to index.php)
     * @param string|null $mimeType
     * @param StdoutFileWriter|null $fileWriter
     */
    public function __construct(
        public readonly Path $filePath,
        public readonly ?string $mimeType,
        public readonly ?StdoutFileWriter $fileWriter = null
    ){
    }

    function onNotReceived()
    {
        throw new DevPanic("FileDownloader not found.");
    }
}
