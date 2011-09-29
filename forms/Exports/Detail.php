<?php

class NetExport_Form_Detail extends Omeka_Form
{
    public function init()
    {
        parent::init();
        $this->addElement('text', 'name', array(
            'label' => 'Export Name',
            'size' => 60,
            'required' => true,
        ));
        $this->addElement('textarea', 'description', array(
            'label' => 'Description',
            'rows' => '10',
            'cols' => '60',
        ));
        $this->addElement('submit', 'submit_add_export', array(
            'label' => 'Add Export',
            'class' => 'submit-medium',
        ));
    }
}
