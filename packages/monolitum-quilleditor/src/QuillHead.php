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
(function() {
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
  
//  var Parchment = Quill.import('parchment');
//  var Delta = Quill.import('delta');
//
//  class ShiftEnterBlot extends Parchment.Embed {} // Actually EmbedBlot
//  ShiftEnterBlot.blotName = 'ShiftEnter';
//  ShiftEnterBlot.tagName = 'br';
//
//  Quill.register(ShiftEnterBlot);
//
//  quill.keyboard.bindings[13].unshift({
//    key: 13,
//    shiftKey: true,
//    handler: function(range) {
//	quill.updateContents(new Delta()
//			     .retain(range.index)
//			     .delete(range.length)
//			     .insert({ \"ShiftEnter\": true }),
//			     'user');
//
//	if (!quill.getLeaf(range.index + 1)[0].next) {
//	  quill.updateContents(new Delta()
//			       .retain(range.index + 1)
//			       .delete(0)
//			       .insert({ \"ShiftEnter\": true }),
//			       'user');
//	}
//
//	quill.setSelection(range.index + 1, Quill.sources.SILENT);
//	return false; // Don't call other candidate handlers
//      }});
      
//  var Inline = Quill.import('blots/inline');
//    
//  var Parchment = Quill.import('parchment');
//
//  class ShiftEnterBlot extends Inline {
//    static create(value) {
//      let node = super.create(value);
//      node.setAttribute('style', \"margin-top:0px;\");
//      node.__rand = value;
//      return node;
//    }
//
//    static formats(domNode) {
//      let blot = Parchment.find(domNode);
//
//      if (blot && blot.parent && blot.parent.children &&
//	  blot.parent.children.head !== blot)
//	return domNode.__rand;
//    }
//  }
//
//  ShiftEnterBlot.blotName = 'ShiftEnter';
//  ShiftEnterBlot.tagName = 'p';
//  ShiftEnterBlot.className = 'shift-enter-class';

//  Inline.order.push(ShiftEnterBlot.blotName);

//  Quill.register(ShiftEnterBlot);
//
//  quill.keyboard.bindings[13].unshift({
//    key: 13,
//	shiftKey: true,
//	handler: function(range) {
//	quill.format('ShiftEnter', 'rand-' + Math.floor(1000000000 * Math.random()));
//
//	return false; // Don't call other candidate handlers
//      }});
 })();
                    ", true)));

        return Rendered::of($link);

    }

}
