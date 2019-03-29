<?php

namespace GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag\Activation;

class Hashtag extends AbstractFieldArray
{

    /**
     * @var $_attributesRenderer Activation
     */
    protected $_activation;

    /**
     * Get activation options.
     *
     * @return Activation
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getActivationRenderer()
    {
        if (!$this->_activation) {
            $this->_activation = $this->getLayout()->createBlock(
                Activation::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }

        return $this->_activation;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'hashtag',
            [
                'label' => __('Hashtag Value'),
                'renderer' => false
            ]
        );
        $this->addColumn(
            'status',
            [
                'label' => __('Status'),
                'renderer' => $this->_getActivationRenderer()
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add More Hashtag');
    }

    /**
     * Prepare existing row data object.
     *
     * @param \Magento\Framework\DataObject $row
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $options = [];
        $customAttribute = $row->getData('status');

        $key = 'option_' . $this->_getActivationRenderer()->calcOptionHash($customAttribute);
        $options[$key] = 'selected = "selected"';
        $row->setData('option_extra_attrs', $options);
    }
}
