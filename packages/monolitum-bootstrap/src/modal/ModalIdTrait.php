<?php

namespace monolitum\bootstrap\modal;

trait ModalIdTrait
{

    /**
     * @var string
     */
    private string $modalId;

    /**
     * @return string
     */
    public function getModalId(): string
    {
        return $this->modalId;
    }

}
