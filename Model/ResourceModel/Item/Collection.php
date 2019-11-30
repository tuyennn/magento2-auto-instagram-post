<?php

namespace GhoSter\AutoInstagramPost\Model\ResourceModel\Item;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{

    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \GhoSter\AutoInstagramPost\Model\Item::class,
            \GhoSter\AutoInstagramPost\Model\ResourceModel\Item::class
        );
    }
}
