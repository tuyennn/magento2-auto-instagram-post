<?php

namespace GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class TestConnection
 * @package GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config
 */
class TestConnection extends Field
{
    /**
     * @var string
     */
    protected $_template = 'GhoSter_AutoInstagramPost::system/config/test_connection.phtml';

    /**
     * Remove scope label
     *
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl(
            'auto_instagram/system_config/testConnection',
            [
                'store' => $this->_request->getParam('store')
            ]
        );
    }

    /**
     * Generate collect button html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            [
                'id' => 'test_connection',
                'label' => __('Test Connection'),
                'style' => 'display: block'
            ]
        );

        return $button->toHtml();
    }
}