<?php

namespace monolitum\quilleditor;

use monolitum\backend\globals\Request_NewId;
use monolitum\backend\params\Path;
use monolitum\bootstrap\BSPage;
use monolitum\core\Find;
use monolitum\frontend\component\CSSLink;
use monolitum\frontend\component\JSScript;
use monolitum\frontend\Head;
use monolitum\frontend\html\HtmlElement;
use monolitum\frontend\html\HtmlElementContent;
use monolitum\frontend\HtmlElementNode;
use monolitum\frontend\Renderable;
use monolitum\frontend\Rendered;
use function monolitum\core\m;

class QuillEditor extends HtmlElementNode
{

    private ?string $editor_id = null;

    private ?string $html_content = null;

    private string $style = 'snow';

    private ?string $container_id = null;

    private ?string $placeholder = null;

    private int $initialHeight = 500;

    public function __construct($builder = null)
    {
        parent::__construct(new HtmlElement("input"), $builder);
        $this->getElement()->setAttribute("type", "hidden");
    }

    /**
     * @param int $initialHeight
     */
    public function setInitialHeight(int $initialHeight): self
    {
        $this->initialHeight = $initialHeight;
        return $this;
    }

    public function setValue($content): self
    {
        $this->html_content = $content;

        $element = $this->getElement();
        $element->setAttribute("value", $content, true);

        return $this;
    }

    public function setContent($content, $raw=false): HtmlElementNode
    {
        $this->setValue($content);
        return $this;
    }

    public function setName(string $name): void
    {
        $this->getElement()->setAttribute("name", $name);
    }

    public function setDisabled(bool $disabled = true): void
    {
        $this->getElement()->setAttribute("disabled", $disabled ? "disabled" : null);
    }

    public function setPlaceholder(string $placeholder): void
    {
        $this->placeholder = $placeholder;
    }


    protected function onAfterBuild(): void
    {
        parent::onAfterBuild();

        /** @var BSPage $page */
        $page = Find::pushAndGet(BSPage::class);
        if(!$page->getConstant("quilleditor-js-css")){
            CSSLink::of(Path::fromRelativeToClass(QuillEditor::class,"res", "quill.custom.css"))->pushSelf();
            CSSLink::of(Path::fromRelativeToClass(QuillEditor::class,"res", "quill.snow.css"))->pushSelf();
            JSScript::of(Path::fromRelativeToClass(QuillEditor::class,"res", "quill.js"))->pushSelf();
            M(new QuillHead());
            $page->setConstant("quilleditor-js-css");
        }

        $this->editor_id = $this->getId();
        if($this->editor_id === null){
            $this->editor_id = Request_NewId::pushAndGet();
            $this->setId($this->editor_id);
        }

        $this->container_id = Request_NewId::pushAndGet();

    }

    public function render(): array|null|Renderable
    {

        $isDisabled = $this->getElement()->getAttribute("disabled") != null;

        return Rendered::of([
            parent::render(),
            (new HtmlElement("div"))
//                ->setAttribute("style", "border: 1px solid #ccc;z-index: 100;")
                ->addChildElement((new HtmlElement("div"))
                    ->setId($this->container_id)
                    ->setAttribute("style", "height: {$this->initialHeight}px;")
                    ->setRequireEndTag(true)
                )
                ->addChildElement((new HtmlElement("script"))
                    ->setContent((new HtmlElementContent("
                    
                    (function(){
                        var icons = Quill.import('ui/icons');
//                        icons['divider'] = '<i class=\"fa fa-grip-lines\" aria-hidden=\"true\"></i>';
                        icons['divider'] = '<i class=\"fa fa-slash\" style=\"-webkit-transform: rotate(142deg);transform: rotate(142deg);\" aria-hidden=\"true\"></i>';

                        const toolbarOptions = [
                          ['bold', 'italic', 'underline', 'strike'],        // toggled buttons
                          ['blockquote', 'code-block', {'divider': 'hr'}],
                          ['link', 'image'],//, 'video', 'formula'],
                        
                          [{ 'header': 1 }, { 'header': 2 }],               // custom button values
                          [{ 'list': 'ordered'}, { 'list': 'bullet' }, { 'list': 'check' }],
                          [{ 'script': 'sub'}, { 'script': 'super' }],      // superscript/subscript
                          [{ 'indent': '-1'}, { 'indent': '+1' }],          // outdent/indent
                          [{ 'direction': 'rtl' }],                         // text direction
                        
                          [{ 'size': ['small', false, 'large', 'huge'] }],  // custom dropdown
                          [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                        
                          [{ 'color': [] }, { 'background': [] }],          // dropdown with defaults from theme
                          [{ 'font': [] }],
                          [{ 'align': [] }],
                        
                          ['clean']                                         // remove formatting button
                        ];
                            const options = {
                            " . (
                                $this->placeholder !== null
                                    ? "placeholder: \""
                                        . addslashes($this->placeholder)
                                        . "\","
                                    : ""
                                ) . "
                              readOnly: " . ($isDisabled ? "true" : "false") . ",
                              modules: {
                                toolbar: toolbarOptions
                              },
                              theme: 'snow'
                            };
                          const quill = new Quill('#" . $this->container_id . "', options);
                          
                          quill.getModule('toolbar').handlers.divider = (value) => {
                            const selection = quill.getSelection(focus = true);
                            let position = 0;
                            // divider will replace any selected text
                            if (!!selection.length) {
                                quill.deleteText(selection);
                            }
                            // if last position in editor, add newline after divider (caret will not be after hr otherwise)
                            if (selection.index === quill.getLength() - 1) {
                                quill.insertText(selection.index, '\\n')
                            }
                            // if at end of block, insert hr after newline character (quill will add a newline after divider otherwise)
//                            if (JSON.stringify(quill.getContents(selection.index, 1)) == JSON.stringify({ ops: [{ insert: \"\\n\" }] })) {
//                                position = selection.index + 1;
//                            } else {
                                position = selection.index;
//                            }
                            quill.insertEmbed(position, 'divider', true);
                            // move selection after divider
                            quill.setSelection(selection.index + 1);
                        }
                        
                         quill.on('text-change', (delta, oldDelta, source) => {
                              if (source == 'api') {
                                console.log('An API call triggered this change.');
                              } else if (source == 'user') {
                                document.getElementById('" . $this->editor_id . "').value = JSON.stringify(quill.getContents().ops);
                                console.log('A user action triggered this change.');
                                console.log(JSON.stringify(quill.getContents().ops));
                              }
                            });
                            var contents = document.getElementById('" . $this->editor_id . "').value;
                            console.log(contents);
                            if(contents) quill.setContents(//new Delta(
                                JSON.parse(contents)
                            //)
                            );
                            })();
                    ", true)))
                )
        ]);

    }

}
