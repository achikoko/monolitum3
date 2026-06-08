<?php

namespace monolitum\backend\files;

use monolitum\model\attr\Attr;
use monolitum\model\Model;

class FileUploadDatabaseModel
{

    function __construct(
        public Model|string     $model,

        public Attr|string      $id,
        public Attr|string      $name,
        public Attr|string      $type,
        public Attr|string      $size,
        public Attr|string      $category,
        public Attr|string      $fileName,

        public Attr|string|null $uploadTimestamp = null,
        public Attr|string|null $deleteTimestamp = null,
    )
    {

    }

}
