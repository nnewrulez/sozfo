<?php
/**
 * Extension to the Textarea to utilise TinyMCE Wysiwyg editor
 *
 * @category   Sozfo
 * @package    Sozfo_Form
 * @subpackage Element
 * @copyright  Copyright (c) 2009 Soflomo.com
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Sozfo_Form_Element_TinyMce extends Zend_Form_Element_Textarea
{
    /**
     * Use formTextarea view helper by default
     * @var string
     */
    public $helper = 'formTinyMce';
}
