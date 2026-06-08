<?php

namespace monolitum\backend\resources;

use monolitum\backend\params\Path;
use monolitum\core\MNode;

class StdoutFileWriter
{

    public function write(MNode $caller, Path $fullPath, ?string $mimeType): void
    {
        $fileToOpen = $fullPath->writePath(false);
        readfile($fileToOpen);
    }

}
