<?php

namespace GhoSter\AutoInstagramPost\Model;

class Item extends \Magento\Framework\Model\AbstractModel {

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('GhoSter\AutoInstagramPost\Model\ResourceModel\Item');
    }
}
