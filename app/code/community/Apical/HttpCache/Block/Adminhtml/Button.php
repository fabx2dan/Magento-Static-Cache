<?php

class Apical_HttpCache_Block_Adminhtml_Button extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    /**
     * Adds flush button to Cache Management.
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element) {
        $this->setElement($element);

        $html = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setType('button')
            ->setClass('scalable')
            ->setLabel('Flush Http Cache')
            ->setOnClick('setLocation(\''.Mage::helper('adminhtml')->getUrl('adminhtml/httpCache/flush').'\')')
            ->toHtml();

        return $html;
    }

}