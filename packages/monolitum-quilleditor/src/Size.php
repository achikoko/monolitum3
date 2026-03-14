<?php

namespace monolitum\quilleditor;

use nadar\quill\InlineListener;
use nadar\quill\Line;

/**
 * Convert Small Inline elements.
 *
 * @author Basil Suter <basil@nadar.io>
 * @since 1.0.0
 */
class Size extends InlineListener
{
    /**
     * {@inheritDoc}
     */
    public function process(Line $line): void
    {
        if ($size = $line->getAttribute('size')) {
            if($size == "small"){
                $this->updateInput($line, '<span style="font-size:smaller">'.$line->getInput().'</span>');
            }else if($size == "large"){
                $this->updateInput($line, '<span style="font-size:larger">'.$line->getInput().'</span>');
            }else if($size == "huge"){
                $this->updateInput($line, '<span style="font-size:larger"><span style="font-size:larger">'.$line->getInput().'</span></span>');
            }
        }
    }
}
