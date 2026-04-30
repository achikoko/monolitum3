<?php

namespace monolitum\backend\files;

use Closure;
use monolitum\core\MNode;
use monolitum\model\values\File;

class FileUploadManager extends MNode
{
    function __construct(Closure $builder)
    {
        parent::__construct($builder);
    }

    public function uploadFile(File $file, string $category)
    {

    }

}
