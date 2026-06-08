<?php

namespace monolitum\backend\files;

use finfo;
use monolitum\model\attr\Attr_File;
use monolitum\model\FileTypeValidator;
use monolitum\model\ValidatedValue;
use monolitum\model\values\File;

class PDFValidator implements FileTypeValidator
{
    const MIME_PDF = "application/pdf";

    /**
     * @inheritDoc
     */
    function validate(ValidatedValue $validatedValue): ?ValidatedValue
    {
        /** @var File $file */
        $file = $validatedValue->getValue();

        if ($file->type === self::MIME_PDF) {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file->path);

            if ($mime === self::MIME_PDF) {
                return $validatedValue;
            }

            return new ValidatedValue(false, error: Attr_File::ERROR_BAD_FORMAT);
        }

        return null;
    }
}
