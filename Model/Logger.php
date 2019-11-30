<?php

namespace GhoSter\AutoInstagramPost\Model;

use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use Magento\Catalog\Model\Product;
use GhoSter\AutoInstagramPost\Model\ItemFactory;

/**
 * Class Logger
 */
class Logger
{
    /**
     * @var \GhoSter\AutoInstagramPost\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Logger constructor.
     * @param ItemFactory $itemFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        ItemFactory $itemFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_itemFactory = $itemFactory;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * Record log after uploading
     *
     * @param $product Product
     * @param $result
     * @param $type
     * @throws \Exception
     */
    public function recordInstagramLog($product, $result, $type = null)
    {
        if ($type) {

            if ($type == InstagramItem::TYPE_SUCCESS) {
                $product->setData('posted_to_instagram', 1);
            } elseif ($type == InstagramItem::TYPE_ERROR) {
                $product->setData('posted_to_instagram', 0);
            }

            $product->getResource()->saveAttribute($product, 'posted_to_instagram');

            /** @var $item InstagramItem */
            $item = $this->_itemFactory->create();
            $item->setProductId($product->getId());
            $item->setType($type);
            $item->setMessages($this->_jsonHelper->jsonEncode($result));
            $item->setCreatedAt(date('Y-m-d h:i:s'));
            $item->save();
        }
    }
}
