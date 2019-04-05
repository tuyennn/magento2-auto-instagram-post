<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Framework\Model\AbstractModel;
use GhoSter\AutoInstagramPost\Api\Data\ItemInterface;

class Item extends AbstractModel implements ItemInterface
{

    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';

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

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    public function setProductId($product_id)
    {
        return $this->setData(self::PRODUCT_ID, $product_id);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    public function getMessages()
    {
        return $this->getData(self::MESSAGES);
    }

    public function setMessages($messages)
    {
        return $this->setData(self::MESSAGES, $messages);
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
