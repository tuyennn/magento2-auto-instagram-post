<?php

namespace GhoSter\AutoInstagramPost\Model;

class Item extends \Magento\Framework\Model\AbstractModel {

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $_dateTime;

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\GhoSter\AutoInstagramPost\Model\ResourceModel\Item::class);
    }
}
