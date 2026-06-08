<?php

namespace monolitum\backend\files;

use finfo;
use monolitum\model\attr\Attr_File;
use monolitum\model\FileTypeValidator;
use monolitum\model\ValidatedValue;
use monolitum\model\values\File;

class ImageFileValidator implements FileTypeValidator
{
    const MIME_JPEG = "image/jpeg";
    const MIME_PNG = "image/png";
    const MIME_GIF = "image/gif";

    private array $supportedTypes;

    public function supportJPEG(): self{
        $supportedTypes[] = self::MIME_JPEG;
        return $this;
    }

    public function supportPNG(): self{
        $supportedTypes[] = self::MIME_PNG;
        return $this;
    }

    public function supportGIF(): self{
        $supportedTypes[] = self::MIME_GIF;
        return $this;
    }

    /**
     * @inheritDoc
     */
    function validate(ValidatedValue $validatedValue): ?ValidatedValue
    {
        /** @var File $file */
        $file = $validatedValue->getValue();

        if ($file->type == self::MIME_JPEG || $file->type == self::MIME_PNG || $file->type == self::MIME_GIF) {

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file->path);

            if ($mime === false) {
                return new ValidatedValue(false, error: Attr_File::ERROR_BAD_FORMAT);
            }

            switch ($mime) {
                case self::MIME_JPEG:
                    if ($file->type === self::MIME_JPEG) {
                        return $validatedValue;
                    }
                    break;
                case self::MIME_PNG:
                    if ($file->type === self::MIME_PNG) {
                        return $validatedValue;
                    }
                    break;
                case self::MIME_GIF:
                    if ($file->type === self::MIME_GIF) {
                        return $validatedValue;
                    }
                    break;
            }

            return new ValidatedValue(false, error: Attr_File::ERROR_BAD_FORMAT);
        }

        return null;
    }
}
