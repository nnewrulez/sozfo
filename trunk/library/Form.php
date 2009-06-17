<?php
class Sozfo_Form extends Zend_Form
{
    public function __construct($options = null)
    {
        $this->addPrefixPath('Sozfo_Form_', 'Sozfo/Form/');
        parent::__construct($options);
    }
}