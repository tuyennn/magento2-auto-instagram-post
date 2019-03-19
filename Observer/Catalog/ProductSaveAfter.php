<?php

namespace GhoSter\AutoInstagramPost\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use GhoSter\AutoInstagramPost\Model\Logger;

class ProductSaveAfter implements ObserverInterface
{

    /**
     * @var \GhoSter\AutoInstagramPost\Helper\Data
     */
    protected $_helper;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var array
     */
    protected $_account;


    /**
     * @var Instagram
     */
    protected $_instagram;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

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
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \GhoSter\AutoInstagramPost\Helper\Data $helper
     * @param Instagram $instagram
     * @param ImageProcessor $imageProcessor
     * @param Logger $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \GhoSter\AutoInstagramPost\Helper\Data $helper,
        Instagram $instagram,
        ImageProcessor $imageProcessor,
        Logger $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action
    )
    {
        $this->_helper = $helper;
        $this->_instagram = $instagram;
        $this->imageProcessor = $imageProcessor;
        $this->_logger = $logger;
        $this->_directoryList = $directoryList;

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

        if (!$this->_helper->isObserverEnabled()) {
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        if (!$product->getData('posted_to_instagram')) {

            // Check and process exist image size
            $image = $this->imageProcessor->processBaseImage($product);

            // Start to Send Image to Instagram
            if ($image) {

                $caption = $this->_helper->getInstagramPostDescription($product);

                if (!empty($this->_account)
                    && isset($this->_account['username'])
                    && isset($this->_account['password'])) {
                    $this->_getInstagram()
                        ->setUser(
                            $this->_account['username'],
                            $this->_account['password']
                        );
                }

                try {

                    if (!$this->_getInstagram()->login()) {
                        $this->_messageManager->addErrorMessage(__('Unauthorized Instagram Account, check your user/password setting'));
                    }

                    $result = $this->_getInstagram()->uploadPhoto($image, $caption);

                    if (empty($result)) {
                        $this->_messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                    }

                    if ($result['status'] === Instagram::STATUS_FAIL) {
                        $this->_logger->recordInstagramLog(
                            $product,
                            $result,
                            InstagramItem::TYPE_ERROR
                        );

                        $this->_messageManager->addComplexErrorMessage(
                            'InstagramError',
                            [
                                'instagram_link' => 'https://help.instagram.com/1631821640426723'
                            ]
                        );
                    }

                    if ($result['status'] === Instagram::STATUS_OK) {
                        $this->_logger->recordInstagramLog(
                            $product,
                            $result,
                            InstagramItem::TYPE_SUCCESS
                        );

                        $this->_messageManager->addComplexSuccessMessage(
                            'InstagramSuccess',
                            [
                                'instagram_link' => 'https://www.instagram.com/p/' . $result['media']['code']
                            ]
                        );
                    }


                } catch (\Exception $e) {
                    $this->_messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                }
            }
        }

    }

    /**
     * Get Instagram Client
     *
     * @return \GhoSter\AutoInstagramPost\Model\Instagram
     */
    private function _getInstagram()
    {
        return $this->_instagram;
    }
}
