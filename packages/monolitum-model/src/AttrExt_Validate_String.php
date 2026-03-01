<?php
namespace monolitum\model;

use Closure;
use monolitum\core\panic\DevPanic;
use monolitum\i18n\TS;

// For the future me: https://stackoverflow.com/questions/4147646/determine-if-utf-8-text-is-all-ascii
class AttrExt_Validate_String extends AttrExt_Validate
{

    private ?string $regex = null;

    private TS|string|null $regexError = null;

    /**
     * @var string[]|TS[]
     */
    private ?array $enums = null;

    private TS|string|null $enumsError = null;

    private ?int $filterValidate = null;

    private TS|string|null $filterValidateError = null;

    private ?int $maxChars = null;

    private TS|string|null $maxCharsError = null;

    private ?Closure $validatorFunction = null;

    private TS|string|null $validatorFunctionError = null;

    private ?Closure $postprocessorFunction = null;

    private bool $trim = false;

    /**
     * @return $this
     */
    public function trim(): self
    {
        $this->trim = true;
        return $this;
    }

    /**
     * @param int $maxChars
     * @param string|TS|null $maxCharsError
     * @return $this
     */
    public function maxChars(int $maxChars, string|TS $maxCharsError = null): self
    {
        $this->maxChars = $maxChars;
        $this->maxCharsError = $maxCharsError;
        return $this;
    }

    /**
     * Set a regular expression to validate the string.
     * It must follow the following pattern "/^...$/"
     * @param string $regex
     * @param string|TS|null $regexError
     * @return $this
     */
    public function regex(string $regex, string|TS|null $regexError = null): self
    {
        $this->regex = $regex;
        $this->regexError = $regexError;
        return $this;
    }

    /**
     * @param array<string|TS|array<string|TS>> $strings
     * @param string|TS|null $enumsError
     * @return $this
     */
    public function enum(array $strings, string|TS|null $enumsError = null): self
    {
        $this->enums = $strings;
        $this->enumsError = $enumsError;
        return $this;
    }

    /**
     * @param int $filterValidate
     * @param string|TS|null $filterValidateError
     * @return $this
     */
    public function filter_validate(int $filterValidate, string|TS $filterValidateError = null): self
    {
        $this->filterValidate = $filterValidate;
        $this->filterValidateError = $filterValidateError;
        return $this;
    }

    /**
     * @param Closure $validatorFunction
     * @param string|TS|null $validatorFunctionError
     * @return $this
     */
    public function func_validator(Closure $validatorFunction, string|TS $validatorFunctionError = null): self
    {
        $this->validatorFunction = $validatorFunction;
        $this->validatorFunctionError = $validatorFunctionError;
        return $this;
    }

    /**
     * @param Closure $postprocessorFunction
     * @return $this
     */
    public function func_postprocessor(Closure $postprocessorFunction): self
    {
        $this->postprocessorFunction = $postprocessorFunction;
        return $this;
    }

    #[\Override]
    public function validate(ValidatedValue $validatedValue): ValidatedValue
    {
        // Transform the value before validating
        if($validatedValue->isWellFormat() && $this->trim){
            $value = $validatedValue->getValue();
            $value = is_string($value) ? trim($value) : $value;
            $validatedValue = new ValidatedValue(
                $validatedValue->isValid(),
                $validatedValue->isWellFormat(),
                $value,
                null,
                $value === null ? "" : strval($value)
            );
        }

        $validatedValue = parent::validate($validatedValue);

        if(!$validatedValue->isValid())
            return $validatedValue;

        $error = false;
        $errorMessage = null;

        if(!$validatedValue->isNull()){
            if($this->enums !== null){
                $found = false;
                foreach ($this->enums as $enumKey => $enumValue){
                    if(is_string($enumKey)){
                        if($validatedValue->getValue() == $enumKey){
                            $found = true;
                            break;
                        }
                    }else if(is_string($enumValue)){
                        if($validatedValue->getValue() == $enumValue){
                            $found = true;
                            break;
                        }
                    }else if(is_array($enumValue)){
                        if($validatedValue->getValue() == $enumValue[0]){
                            $found = true;
                            break;
                        }
                    }else{
                        throw new DevPanic("Enum constant not found");
                    }
                }
                if(!$found){
                    $error = true;
                    $errorMessage = $this->enumsError;
                }
            }
            if(!$error && $this->maxChars !== null){
                if(strlen($validatedValue->getValue()) > $this->maxChars) {
                    $error = true;
                    $errorMessage = $this->maxCharsError;
                }
            }
            if(!$error && $this->regex !== null){
                if(!preg_match($this->regex, $validatedValue->getValue())) {
                    $error = true;
                    $errorMessage = $this->regexError;
                }
            }
            if(!$error && $this->filterValidate !== null){
                if(!filter_var($validatedValue->getValue(), $this->filterValidate)) {
                    $error = true;
                    $errorMessage = $this->filterValidateError;
                }
            }
            if(!$error && $this->validatorFunction !== null){
                $vf = $this->validatorFunction;
                $result = $vf($validatedValue->getValue());
                if(!$result){
                    $error = true;
                    $errorMessage = $this->validatorFunctionError;
                }
            }
        }

        if($error){
            return new ValidatedValue(false, true, $validatedValue->getValue(), $errorMessage, $validatedValue->getStrValue());
        }else{

            if($this->postprocessorFunction !== null){
                $vf = $this->postprocessorFunction;
                $result = $vf($validatedValue->getValue());
                return new ValidatedValue($validatedValue->isValid(), $validatedValue->isWellFormat(), $result, $validatedValue->getError(), $validatedValue->getStrValue());
            }

            return $validatedValue;
        }
    }

    public function hasEnum(): bool
    {
        return $this->enums != null;
    }

    /**
     * @return string[]
     */
    public function getEnums(): array
    {
        return $this->enums;
    }

    public function getMaxChars(): ?int
    {
        return $this->maxChars;
    }

    public function computeMaxChars(): ?int
    {
        if($this->maxChars !== null){
            return $this->maxChars;
        }

        if($this->enums !== null){
            $maxLen = 64; // Set a minimum of 64 when enums, to let space for new values
            foreach (EnumUtils::iterateKeys($this->enums) as $enumKey){
                $maxLen = max($maxLen, strlen($enumKey));
            }
            return $maxLen;
        }

        return null;
    }

    public function computeAsciiness(): bool
    {
        if($this->enums !== null)
            return true;
        return false;
    }

    public function getEnumString(mixed $value): string|TS|null
    {
        return EnumUtils::getStringFromEnumArray($this->enums, $value);
    }

}

