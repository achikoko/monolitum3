<?php

namespace monolitum\frontend\form;

use monolitum\backend\params\ParamsManager;
use monolitum\core\panic\DevPanic;
use monolitum\i18n\TS;
use monolitum\model\attr\Attr;
use monolitum\model\AttrExt_Validate;
use monolitum\model\Entity;
use monolitum\model\Model;
use monolitum\model\ValidatedValue;

abstract class Form_Validator
{
    use Trait_Form_Validate_Attrs;

    /**
     * @var array<string, ValidatedValue>
     */
    protected array $build_validatedValues = [];

    /**
     * @var array<string, ValidatedValue>
     */
    protected array $overwritten_validatedValues = [];

    /**
     * @var bool
     */
    protected bool $build_allValid = false;

    /**
     * @var bool
     */
    protected bool $build_isAlreadyValidated = false;

    /**
     * @var Form
     */
    protected ?Form $form = null;

    /**
     * @param Form $form
     * @return void
     */
    function _setForm(Form $form): void
    {
        if($this->form !== null)
            throw new DevPanic("Validator can only be used in one Form");
        $this->form = $form;

    }

    function _validateAll(): void
    {
        $this->build_allValid = true;
        $this->build_isAlreadyValidated = true;
    }

    /**
     * @return bool
     */
    public function isAllValid(): bool
    {
        if(!$this->build_isAlreadyValidated)
            $this->_validateAll();
        return $this->build_allValid;
    }

    /**
     * Read the value from the external source, validate it and return it.
     * @param Attr|string $attr
     * @return ?ValidatedValue
     */
    abstract function getValidatedValue(Attr|string $attr): ?ValidatedValue;

    /**
     * Ignore any validation and return the default value.
     * It will fetch the current editing entity or, if not, if the dev set any default value to the ext
     * @param Attr|string $attr
     * @return ?ValidatedValue
     */
    abstract function getDefaultValue(Attr|string $attr): ?ValidatedValue;

    /**
     * @param string $prefix prefix of the attribute that must be set
     * @return ?ValidatedValue if found, executed action string
     */
    abstract function validateSubmissionKey(string $prefix): ?ValidatedValue;

    /**
     * @param Entity $entity
     * @return void
     */
    public function writeValidValuesOn(Entity $entity): void
    {
        throw new DevPanic("Writing values to an entity not supported in this validator");
    }

    /**
     * @param string|Model $model
     * @param string|Attr $attr
     * @return ValidatedValue
     */
    public static function pushFindValidatedValue(Model|string $model, Attr|string $attr): ValidatedValue
    {
        $paramsManager = ParamsManager::findSelf();

        $model = Model::pushFindByName($model);
        $attr = $model->getAttr($attr);

        $validated = $paramsManager->validate($model, $attr);

        /** @var AttrExt_Validate $ext */
        $ext = $attr->findExtension(AttrExt_Validate::class);
        if(!$ext)
            return $validated;

        return $ext->validate($validated);

    }

    /**
     * @param string|Attr $attrId
     * @return Attr
     */
    public abstract function getAttr(Attr|string $attrId): Attr;

    /**
     * @param string|Attr $attrId
     * @return bool
     */
    public function isAttrInValidateList(Attr|string $attrId): bool
    {
        $attr = $this->getAttr($attrId);
        $inArray = in_array($attr->getId(), $this->validate_attrs);
        return $this->validate_attrs_all ^ $inArray;
    }

    /**
     * @param string|Attr $attrId
     * @param ValidatedValue $value
     */
    public function overwriteValidatedValue(Attr|string $attrId, ValidatedValue $value): void
    {
        $attr = $this->getAttr($attrId);
        $this->overwritten_validatedValues[$attr->getId()] = $value;
    }

    /**
     * @param string|Attr $attrId
     * @param string|TS $errorString
     */
    public function invalidate(Attr|string $attrId, TS|string $errorString)
    {
        $attr = $this->getAttr($attrId);
        $validatedValue = $this->getValidatedValue($attr->getId());
        $this->overwritten_validatedValues[$attr->getId()] = new ValidatedValue(
            false, $validatedValue->isWellFormat(), $validatedValue->getValue(), $errorString, $validatedValue->getStrValue()
        );
    }

}
