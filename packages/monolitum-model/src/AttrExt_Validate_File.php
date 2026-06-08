<?php
namespace monolitum\model;

use monolitum\model\attr\Attr_File;
use monolitum\model\values\File;

class AttrExt_Validate_File extends AttrExt_Validate
{

    private ?int $maxSize = 1000000;

    /**
     * @var array<FileTypeValidator>
     */
    private array $fileTypeValidators = [];

    /**
     * Max size in bytes
     * @param int $maxSize
     * @return $this
     */
    public function maxSize(int $maxSize): self
    {
        $this->maxSize = $maxSize;
        return $this;
    }

    public function addValidator(FileTypeValidator $fileTypeValidator): self
    {
        $this->fileTypeValidators[] = $fileTypeValidator;
        return $this;
    }

    #[\Override]
    public function validate(ValidatedValue $validatedValue): ValidatedValue
    {

        $validatedValue = parent::validate($validatedValue);

        if(!$validatedValue->isValid() || $validatedValue->isNull())
            return $validatedValue;

        /** @var File $file */
        $file = $validatedValue->getValue();

        if ($this->maxSize !== null && $file->size > $this->maxSize) {
            return new ValidatedValue(false, false, null, Attr_File::ERROR_MAX_SIZE);
        }

        foreach($this->fileTypeValidators as $validator) {
            $result = $validator->validate($validatedValue);
            if($result !== null){
                return $result;
            }
        }

        return new ValidatedValue(false, false, null, Attr_File::ERROR_NOT_VALIDATED);
    }

}

