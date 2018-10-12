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

    /**
     * @var array
     */
    protected $_account;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\ItemFactory
     */
    protected $_itemFactory;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\Instagram
     */
    protected $_instagram;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

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
     * @param \GhoSter\AutoInstagramPost\Model\ItemFactory $itemFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \GhoSter\AutoInstagramPost\Helper\Data $helper,
        \GhoSter\AutoInstagramPost\Model\Instagram $instagram,
        \GhoSter\AutoInstagramPost\Model\ItemFactory $itemFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action

    )
    {
        $this->_helper = $helper;
        $this->_instagram = $instagram;
        $this->_itemFactory = $itemFactory;
        $this->_logger = $logger;
        $this->_directoryList = $directoryList;
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
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    )
    {
        if (!$this->_helper->isModuleEnabled()) {
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        if (!$product->getData('posted_to_instagram')) {
            $baseDir = $this->_directoryList->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';

            $instagram = $this->getInstagram();

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
            if (!empty($image)) {

                $caption = $this->_helper->getInstagramPostDescription($product);

                if (!empty($this->_account)) {
                    $instagram->setUser($this->_account['username'], $this->_account['password']);
                }

                try {

                    if (!$this->loginInstagram()) {
                        $this->_messageManager->addErrorMessage(__('Unauthorized Instagram Account, check your user/password setting'));
                    }

                    $result = $instagram->uploadPhoto($image, $caption);

                    if (empty($result)) {
                        $this->_messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                    }

                    if ($result['status'] === 'fail') {
                        $this->_messageManager->addComplexErrorMessage(
                            'InstagramError',
                            [
                                'instagram_link' => 'https://help.instagram.com/1631821640426723'
                            ]
                        );
                    }

                    if ($result['status'] === 'ok') {

                        $product->setData('posted_to_instagram', 1);
                        $product->getResource()->saveAttribute($product, 'posted_to_instagram');

                        /** @var $item \GhoSter\AutoInstagramPost\Model\Item */
                        $item = $this->_itemFactory->create();
                        $item->setProductId($product->getId());
                        $item->setType('success');
                        $item->setMessages($this->_jsonHelper->jsonEncode($result));
                        $item->setCreatedAt(date('Y-m-d h:i:s'));
                        $item->save();

                        $this->_messageManager->addComplexSuccessMessage(
                            'InstagramSuccess',
                            [
                                'instagram_link' => 'https://www.instagram.com/p/' . $result['media']['code']
                            ]
                        );
                    }


                } catch (\Exception $e) {
                    $this->_logger->critical($e->getMessage());
                }
            }
        }

    }

    /**
     * Get Instagram Client
     *
     * @return \GhoSter\AutoInstagramPost\Model\Instagram
     */
    private function getInstagram()
    {
        return $this->_instagram;
    }

    /**
     * @return array
     * @throws \Exception
     */
    private function loginInstagram()
    {
        return $this->getInstagram()->login();
    }
}
