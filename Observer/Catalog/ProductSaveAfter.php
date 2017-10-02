<?php

namespace GhoSter\AutoInstagramPost\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;

class ProductSaveAfter implements ObserverInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \GhoSter\AutoInstagramPost\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    protected $_account;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\Item
     */
    protected $_item;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\Instagram
     */
    protected $_instagram;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directory_list;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Action
     */
    protected $_action;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \GhoSter\AutoInstagramPost\Helper\Data $helper
     * @param \GhoSter\AutoInstagramPost\Model\Instagram $instagram
     * @param \GhoSter\AutoInstagramPost\Model\Item $item
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory_list
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \GhoSter\AutoInstagramPost\Helper\Data $helper,
        \GhoSter\AutoInstagramPost\Model\Instagram $instagram,
        \GhoSter\AutoInstagramPost\Model\Item $item,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action

    )
    {
        $this->_objectManager = $objectManager;
        $this->_helper = $helper;
        $this->_instagram = $instagram;
        $this->_logger = $logger;
        $this->_directory_list = $directory_list;
        $this->_jsonHelper = $jsonHelper;

        $this->_account = $this->_helper->getAccountInformation();
        $this->_messageManager = $context->getMessageManager();
        $this->_storeManager = $storeManager;
        $this->_action = $action;

    }


    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    )
    {

        $currentStore = $this->_storeManager->getStore();

        if (!$this->_helper->isModuleEnabled()) {
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $product = $observer->getProduct();

        $item = $this->_objectManager->create('GhoSter\AutoInstagramPost\Model\Item');

        if (!$product->getData('posted_to_instagram')) {
            $image = '';
            $baseDir = $this->_directory_list->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';

            $account = $this->_account;
            $instagram = $this->_instagram;

            if ($product->getImage() !== 'no_selection') {
                $baseImage = $product->getImage();
            } elseif ($product->getSmallImage() !== 'no_selection') {
                $baseImage = $product->getSmallImage();
            } else {
                $baseImage = $product->getThumbnail();
            }

            if (!$baseImage) {
                $image = $this->_helper->getDefaultImage();
            }

            // Check and process exist image size
            if ($baseImage) {
                $imageDir = $baseDir . $baseImage;
                if (strpos($baseImage, '.tmp') !== false) {
                    $baseImage = str_replace('.tmp', '', $baseImage);
                    $imageDir = str_replace('media', 'media' . DIRECTORY_SEPARATOR . 'tmp', $baseDir) . $baseImage;
                }
                if (file_exists($imageDir)) {
                    list($width, $height, $type, $attr) = getimagesize($imageDir);
                    $imageSize = $width;
                    if ($height > $width) {
                        $imageSize = $height;
                    }
                    if ($imageSize < 320) {
                        $imageSize = 800;
                    }

                    $image = $this->_helper->getResizeImage($imageDir, $baseImage, $imageSize);

                }
            }

            // Start to Send Image to Instagram
            if ($image != '') {

                $caption = $this->_helper->getInstagramPostDescription($product);

                if (!empty($account)) {
                    $instagram->setUser($account['username'], $account['password']);
                }

                try {

                    $instagram->login();

                    $result = $instagram->uploadPhoto($image, $caption);

                    if ($result['status'] === 'ok') {

                        $this->_action->updateAttributes([$product->getId()], ['posted_to_instagram' => 1], $currentStore->getId());

                        $this->_messageManager->addSuccessMessage(__('The product has been posted to https://www.instagram.com/p/' . $result['media']['code']));

                        $item->setProductId($product->getId());
                        $item->setType('success');
                        $item->setMessages($this->_jsonHelper->jsonEncode($result));
                        $item->setCreatedAt(date('Y-m-d h:i:s'));
                        $item->save();

                    }

                    if ($result['status'] === 'fail') {

                        $this->_messageManager->addErrorMessage(__($result['message'] . '. Please check product\'s images again, https://help.instagram.com/1631821640426723'));
                    }


                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }
        }

    }
}
