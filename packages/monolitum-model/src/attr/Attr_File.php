<?php
namespace monolitum\model\attr;

use monolitum\model\ValidatedValue;
use monolitum\model\values\File;

class Attr_File extends AbstractAttr
{

    const ERROR_NO_ERROR = UPLOAD_ERR_OK;
    const ERROR_INI_SIZE = UPLOAD_ERR_INI_SIZE;
    const ERROR_FORM_SIZE = UPLOAD_ERR_FORM_SIZE;
    const ERROR_PARTIAL = UPLOAD_ERR_PARTIAL;
    const ERROR_NO_FILE = UPLOAD_ERR_NO_FILE;
    const ERROR_NO_TMP_DIR = UPLOAD_ERR_NO_TMP_DIR;
    const ERROR_CANT_WRITE = UPLOAD_ERR_CANT_WRITE;
    const ERROR_EXTENSION = UPLOAD_ERR_EXTENSION;
    const ERROR_BAD_FORMAT = 9;
    const ERROR_MULTIPLE_NOT_SUPPORTED = 10;
    const ERROR_MAX_SIZE = 11;
    const ERROR_NOT_VALIDATED = 12;
    const ERROR_UNKNOWN = 20;

    #[\Override]
    public function validate($value): ValidatedValue
    {
        // TODO $value is an array of attributes
        if($value === null){
            return new ValidatedValue(true); // Null
        }else if(is_array($value)){
            // TODO https://www.php.net/manual/en/features.file-upload.php

            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (
                !isset($value['error']) ||
                is_array($value['error'])
            ) {
                return new ValidatedValue(false, false, null, Attr_File::ERROR_UNKNOWN);
            }

            // Check $_FILES['upfile']['error'] value.
            switch ($value['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_NO_FILE);
                case UPLOAD_ERR_INI_SIZE:
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_INI_SIZE);
                case UPLOAD_ERR_FORM_SIZE:
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_FORM_SIZE);
                case UPLOAD_ERR_PARTIAL:
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_PARTIAL);
                default:
                    return new ValidatedValue(false, false, null, Attr_File::ERROR_UNKNOWN);
            }

            $name = $value['name'];
            if(is_array($name) && count($name) > 0){
                // Multiple is not supported
                return new ValidatedValue(false, false, null, Attr_File::ERROR_MULTIPLE_NOT_SUPPORTED);
            }

            // This type is reported, we cannot trust it
            $type = $value['type'];
            if(is_array($type) && count($type) > 0){
                // Multiple is not supported
                return new ValidatedValue(false, false, null, Attr_File::ERROR_MULTIPLE_NOT_SUPPORTED);
            }
            $temp_name = $value['tmp_name'];
            if(is_array($temp_name) && count($temp_name) > 0){
                // Multiple is not supported
                return new ValidatedValue(false, false, null, Attr_File::ERROR_MULTIPLE_NOT_SUPPORTED);
            }
            $size = $value['size'];
            if(is_array($size) && count($size) > 0){
                // Multiple is not supported
                return new ValidatedValue(false, false, null, Attr_File::ERROR_MULTIPLE_NOT_SUPPORTED);
            }

            return new ValidatedValue(true, true, new File($name, $type, $size, $temp_name));

        }

        return new ValidatedValue(false);
    }

    #[\Override]
    public function stringValue($value): string
    {
        if($value instanceof File){
            return $value->name;
        }
        return "";
    }
}

