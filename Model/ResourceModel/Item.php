<?php

namespace GhoSter\AutoInstagramPost\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Item
 * @package GhoSter\AutoInstagramPost\Model\ResourceModel
 */
class Item extends AbstractDb
{

    /** @var DateTime */
    protected $_date;

    /**
     * Construct
     *
     * @param Context $context
     * @param DateTime $date
     * @param string|null $resourcePrefix
     */
    public function __construct(
        Context $context,
        DateTime $date,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->_date = $date;
    }

    /**
     * Define main table. Define other tables name
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('ghoster_instagram_auto_post', 'id');
    }
}
