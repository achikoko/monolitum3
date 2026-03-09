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

}
