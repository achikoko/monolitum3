<?php
/*
 * Copyright 2008 Sven Sanzenbacher
 *
 * This file is part of the naucon package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace monolitum\frontend\html;

use monolitum\core\util\ServerLogger;

/**
 * Html Builder Class
 *
 * @package    HtmlBuilder
 * @author     Sven Sanzenbacher
 */
class HtmlBuilder
{
    /**
     * @access      protected
     * @var         array                   elements require end tag
     */
    protected $_elementsRequireEndTag = array(
        'select','script','i'
    );

    /**
     * @access      protected
     * @var         array                   black list of attributes witch are not filtered
     */
    protected $_nonSanitizedAttributes = array('onclick',
        'ondblclick',
        'onmousedown',
        'onmouseup',
        'onmouseover',
        'onmousemove',
        'onmouseout',
        'onkeypress',
        'onkeydown',
        'onkeyup'
    );

    /**
     * @access      protected
     * @param       HtmlElement        $htmlElement
     * @return      string
     */
    protected function renderAttributes(HtmlElement $htmlElement): string
    {
        $output = '';
        if($htmlElement->hasClasses()){
            $output .= " class='" . implode(" ", $htmlElement->getClasses()) . "'";
        }
        if($htmlElement->hasStyle()){
            $output .= " style='" . $htmlElement->style()->write() . "'";
        }
        if ($htmlElement->hasAttributes()) {
            $attributes = array();
            $attributeArr = $htmlElement->getAttributes();
            foreach ($attributeArr as $key => $value) {
                // Run filter var to make it able to append in html
                if (!in_array($key, $this->_nonSanitizedAttributes) && !$htmlElement->isAttributeNotSanitized($key)) {
                    $value = filter_var($value, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_ENCODE_AMP);
                }
                $attributes[] = $key . '="' . $value . '"';
            }
            $output .= ' ' . implode(' ', $attributes);
        }
        return $output;
    }

    /**
     * @param       HtmlElement        $htmlElement
     * @return      string                  html output
     */
    public function render(HtmlElement $htmlElement, $depth = 0): string
    {
        if($depth > 1000){
            ServerLogger::log("Infinite loop detected.");
        }

        $output = '<' . $htmlElement->getTag();
        $output.= $this->renderAttributes($htmlElement);

        if ($htmlElement->hasChildElements()) {
            $output .= '>';
            $output.= $this->renderContent($htmlElement, $depth+1);
            $output.= '</' . $htmlElement->getTag() . '>';
        } elseif (in_array($htmlElement->getTag(), $this->_elementsRequireEndTag) || $htmlElement->requireEndTag()) {
            $output.= '>';
            $output .= '</' . $htmlElement->getTag() . '>';
        } else {
            $output .= ' />';
        }
        return $output;
    }

    /**
     * @param       HtmlElement        $htmlElement
     * @return      string                  html output
     */
    public function renderContent(HtmlElement $htmlElement, int $depth): string
    {
        $output = '';
        if ($htmlElement->hasChildElements()) {
            foreach ($htmlElement->getChildElementCollection() as $childElement) {
                if ($childElement instanceof HtmlElementContent) {
                    if($childElement->raw){
                        $output.= $childElement->content;
                    }else{
                        $output.= filter_var($childElement->content, FILTER_SANITIZE_FULL_SPECIAL_CHARS, FILTER_FLAG_ENCODE_AMP);
                    }
                } else {
                    $output.= $this->render($childElement, $depth);
                }
            }
        }
        return $output;
    }

    /**
     * @param       HtmlElement        $htmlElement
     * @return      string                  html output
     */
    public function renderStartTag(HtmlElement $htmlElement): string
    {
        $output = '<' . $htmlElement->getTag();
        $output.= $this->renderAttributes($htmlElement);
        $output .= '>';
        return $output;
    }

    /**
     * @param       HtmlElement        $htmlElement
     * @return      string                  html output
     */
    public function renderEndTag(HtmlElement $htmlElement): string
    {
        $output = '</' . $htmlElement->getTag() . '>';
        return $output;
    }
}
