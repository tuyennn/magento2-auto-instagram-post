<?php
namespace GhoSter\AutoInstagramPost\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface ItemInterface extends ExtensibleDataInterface {
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return void
     */
    public function setId($id);

    /**
     * @return integer
     */
    public function getProductId();


    /**
     * @param integer $product_id
     * @return void
     */
    public function setProductId($product_id);


    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     * @return void
     */
    public function setType($type);


    /**
     * @return string
     */
    public function getMessages();

    /**
     * @param string $messages
     * @return void
     */
    public function setMessages($messages);

}