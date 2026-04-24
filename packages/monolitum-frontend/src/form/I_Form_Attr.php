<?php

namespace monolitum\frontend\form;

/**
 * Interface to mark a component that is a form attribute. Form will call its afterBuildForm() method.
 */
interface I_Form_Attr
{

    /**
     * Called by the form, when it's just built and validated.
     * Is called before to let attributes being built and generate all required fields and submit buttons.
     * @return void
     */
    public function onBeforeBuildForm(): void;

    /**
     * Called by the form, when it's just built and validated.
     * This method is called at the end because the user may be invalidated some fields.
     * @return void
     */
    public function onAfterBuildForm(): void;

}
