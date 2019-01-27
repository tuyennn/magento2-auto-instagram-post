<?php

namespace GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config;

class Hashtag extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{

    /**
     * @var $_attributesRenderer \GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag\Activation
     */
    protected $_activation;

    /**
     * Get activation options.
     *
     * @return \GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag\Activation
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getActivationRenderer()
    {
        if (!$this->_activation) {
            $this->_activation = $this->getLayout()->createBlock(
                '\GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag\Activation',
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
        $this->addColumn('hashtag', ['label' => __('Hashtag Value'), 'renderer' => false]);
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
