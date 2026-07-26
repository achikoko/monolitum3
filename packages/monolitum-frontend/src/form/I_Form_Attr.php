<?php

namespace monolitum\frontend\form;

/**
 * Interface to mark a component that is a form attribute. Form will call its afterBuildForm() method.
 */
interface I_Form_Attr
{

    /**
     * Called by the form, before deciding if it must be validated or not.
     * This method can call Form->setNotValidate() to prevent it from being validated.
     * Is called after building the form and CSRF token being validated, to let attributes already built to modify or add fields and submit buttons.
     * @return void
     */
    public function onCheckForm(): void;

    /**
     * Called by the form, only when being validated, just before. Validator now has the attributes that will validate,
     * if getValidatedValue() it will do it before returning it. Keep in mind that after this method is run, the actual validation happens
     * and then will know if the whole form is valid or not.
     * @return void
     */
    public function onBeforeValidateForm(): void;

    /**
     * Called by the form, only when being validated, just after.
     * This method is called at the end because the user may be invalidated some fields.
     * @return void
     */
    public function onAfterValidateForm(): void;

    /**
     * Called by the form if the form is not going to be validated.
     * @return void
     */
    public function onNotValidateForm(): void;

}
