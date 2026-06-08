<?php

namespace monolitum\backend\resources;

class AllowedExtension
{

    public function __construct(public ?StdoutFileWriter $writer = null, public ?string $mimeType = null)
    {

    }

}
