<?php
namespace monolitum\model;

use Closure;
use monolitum\i18n\TS;
use monolitum\model\enum\Enumeration;

// For the future me: https://stackoverflow.com/questions/4147646/determine-if-utf-8-text-is-all-ascii
class AttrExt_Validate_String extends AttrExt_Validate
{

    private ?string $regex = null;

    private TS|string|null $regexError = null;

    private ?Enumeration $enums = null;

    private TS|string|null $enumsError = null;

    private ?int $filterValidate = null;

    private TS|string|null $filterValidateError = null;

    private ?int $maxChars = null;

    private TS|string|null $maxCharsError = null;

    /**
     * @var Closure|null (VALUE, ENTITY) -> bool // TODO change by context
     */
    private ?Closure $postStringValidatorFunction = null;

    private TS|string|null $postStringValidatorFunctionError = null;

    /**
     * @var Closure|null (PostStringProcessContext) => void
     */
    private ?Closure $postStringProcessorFunction = null;

    private bool $trim = false;
    private bool $nullifyEmpty = false;

    public function trim(): self
    {
        $this->trim = true;
        return $this;
    }

    public function nullifyEmpty(): self
    {
        $this->nullifyEmpty = true;
        return $this;
    }


    /**
     * @param int $maxChars
     * @param string|TS|array|null $maxCharsError
     * @return $this
     */
    public function maxChars(int $maxChars, string|TS|array|null $maxCharsError = null): self
    {
        $this->maxChars = $maxChars;
        $this->maxCharsError = is_array($maxCharsError) ? TS::from($maxCharsError) : $maxCharsError;
        return $this;
    }

    /**
     * Set a regular expression to validate the string.
     * It must follow the following pattern "/^...$/"
     * @param string $regex
     * @param string|TS|array|null $regexError
     * @return $this
     */
    public function regex(string $regex, string|TS|array|null $regexError = null): self
    {
        $this->regex = $regex;
        $this->regexError = is_array($regexError) ? TS::from($regexError) : $regexError;
        return $this;
    }

    /**
     * @param array<string|TS|array<string|TS>> $strings
     * @param string|TS|null $enumsError
     * @return $this
     */
    public function enum(array|Enumeration $strings, string|TS|null $enumsError = null): self
    {
        $this->enums = is_array($strings) ? Enumeration::fromArray($strings) : $strings;
        $this->enumsError = $enumsError;
        return $this;
    }

    public function filter_validate(int $filterValidate, string|TS|array|null $filterValidateError = null): self
    {
        $this->filterValidate = $filterValidate;
        $this->filterValidateError = is_array($filterValidateError) ? TS::from($filterValidateError) : $filterValidateError;
        return $this;
    }

    /**
     * The given function will be executed after validating nullability AND all string constraints. But before
     * executing the "postStringProcess" function if given one.
     * @param Closure $validatorFunction
     * @param string|TS|null $validatorFunctionError
     * @return $this
     */
    public function postStringValidate(Closure $validatorFunction, string|TS $validatorFunctionError = null): self
    {
        $this->postStringValidatorFunction = $validatorFunction;
        $this->postStringValidatorFunctionError = $validatorFunctionError;
        return $this;
    }

    /**
     * @param Closure $postprocessorFunction
     * @return $this
     */
    public function postProcessString(Closure $postprocessorFunction): self
    {
        $this->postStringProcessorFunction = $postprocessorFunction;
        return $this;
    }

    #[\Override]
    public function validate(ValidatedValue $validatedValue): ValidatedValue
    {
        // Transform the value before validating
        if($validatedValue->isWellFormat() && ($this->trim || $this->nullifyEmpty)){
            $value = null;

            if($this->trim){
                $value = $validatedValue->getValue();
                $value = is_string($value) ? trim($value) : $value;
            }

            if($value !== null && $this->nullifyEmpty && strlen($value) == 0){
                $value = null;
            }

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
                $found = $this->enums->keyExist($validatedValue->getValue());
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
            if(!$error && $this->postStringValidatorFunction !== null){
                $vf = $this->postStringValidatorFunction;
                $result = $vf($validatedValue->getValue());
                if(!$result){
                    $error = true;
                    $errorMessage = $this->postStringValidatorFunctionError;
                }
            }
        }

        if($error){
            return new ValidatedValue(false, true, $validatedValue->getValue(), $errorMessage, $validatedValue->getStrValue());
        }else{

            if($this->postStringProcessorFunction !== null){
                $context = new PostProcessStringContext($validatedValue->getValue());
                call_user_func($this->postStringProcessorFunction, $context);
                if(!$context->getResultValid()){
                    $validatedValue = new ValidatedValue(
                        false, true,
                        $validatedValue->getValue(),
                        $context->getResultError(),
                        $validatedValue->getStrValue()
                    );
                }else if($context->isPostProcessed()){
                    $validatedValue = new ValidatedValue(
                        true, true,
                        $context->getPostProcessResult(),
                        null,
                        $context->getPostProcessResult() ?? "",
                    );
                }
            }

            return $validatedValue;
        }
    }

    public function hasEnum(): bool
    {
        return $this->enums != null;
    }

    public function getEnums(): ?Enumeration
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
            foreach ($this->enums as $enumKey => $enumValue){
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

    public function getEnumString(mixed $key): string|TS|null
    {
        return $this->enums->getLabel($key);
    }

}

