<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Framework\Model\AbstractModel;
use GhoSter\AutoInstagramPost\Api\Data\ItemInterface;

/**
 * Item Model
 *
 * Class Item
 */
class Item extends AbstractModel implements ItemInterface
{

    const TYPE_SUCCESS = 'success';
    const TYPE_ERROR = 'error';

    /**
     * Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Item::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getProductId()
    {
        return $this->getData(self::PRODUCT_ID);
    }

    /**
     * {@inheritdoc}
     */
    public function setProductId($product_id)
    {
        return $this->setData(self::PRODUCT_ID, $product_id);
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessages()
    {
        return $this->getData(self::MESSAGES);
    }

    /**
     * {@inheritdoc}
     */
    public function setMessages($messages)
    {
        return $this->setData(self::MESSAGES, $messages);
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}
