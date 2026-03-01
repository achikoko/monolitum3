<?php

namespace monolitum\quilleditor;

use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;


class QuillHead extends Head{

    public function render(): Renderable|array|null
    {
        $link = (new HtmlElement("script"))
            ->setContent((new HtmlElementContent("
                        const BlockEmbed = Quill.import('blots/block/embed');
                        
//                        class Hr extends BlockEmbed {
//                          static blotName = 'divider';
//                          static className = 'my-hr';
//                          static tagName = 'hr';
//                            static create(value) {
//                                let node = super.create(value);
//                                // give it some margin
//                                node.setAttribute('style', \"height:0px; margin-top:10px; margin-bottom:10px;\");
//                                return node;
//                            }
//                        }
//                        var customHrHandler = function(){
//                            // get the position of the cursor
//                            var range = quill.getSelection();
//                            if (range) {
//                                // insert the <hr> where the cursor is
//                                quill.insertEmbed(range.index,\"hr\",\"null\")
//                            }
//                        }
//                        
//                        Quill.register({
//                            'formats/hr': Hr
//                        });
                        class DividerBlot extends BlockEmbed {
                            static blotName = 'divider';
                            static tagName = 'hr';
                        }
                        Quill.register(DividerBlot);
                    ", true)));

        return Rendered::of($link);

    }

}
