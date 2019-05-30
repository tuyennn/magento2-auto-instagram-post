<?php

namespace GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag;

use Magento\Framework\View\Element\Context;
use Magento\Framework\View\Element\Html\Select;
use Magento\Config\Model\Config\Source\Enabledisable;

/**
 * Class Activation
 * @package GhoSter\AutoInstagramPost\Block\Adminhtml\System\Config\Hashtag
 */
class Activation extends Select
{
    /**
     * Model Enabledisable
     *
     * @var Enabledisable
     */
    protected $_enableDisable;

    /**
     * Activation constructor.
     *
     * @param Context $context
     * @param Enabledisable $enableDisable
     * @param array $data
     */
    public function __construct(
        Context $context,
        Enabledisable $enableDisable,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_enableDisable = $enableDisable;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Parse to html.
     *
     * @return mixed
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            $attributes = $this->_enableDisable->toOptionArray();

            foreach ($attributes as $attribute) {
                $this->addOption($attribute['value'], $attribute['label']);
            }
        }

        return parent::_toHtml();
    }
}
