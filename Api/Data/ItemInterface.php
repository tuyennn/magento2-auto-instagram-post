<?php

namespace GhoSter\AutoInstagramPost\Api\Data;


interface ItemInterface
{

    const ID = 'id';
    const PRODUCT_ID = 'product_id';
    const TYPE = 'type';
    const MESSAGES = 'messages';
    const CREATED_AT = 'created_at';

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

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param $createdAt
     * @return void
     */
    public function setCreatedAt($createdAt);
}