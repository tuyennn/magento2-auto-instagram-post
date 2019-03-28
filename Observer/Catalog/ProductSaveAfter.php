<?php

namespace GhoSter\AutoInstagramPost\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use GhoSter\AutoInstagramPost\Model\Logger as InstagramLogger;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;
use Psr\Log\LoggerInterface;


class ProductSaveAfter implements ObserverInterface
{

    /**
     * @var InstagramHelper
     */
    protected $instagramHelper;

    /**
     * @var InstagramConfig
     */
    protected $config;

    /**
     * @var InstagramLogger
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var array
     */
    protected $_account;


    /**
     * @var Instagram
     */
    private $_instagram;

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
     * @param InstagramHelper $instagramHelper
     * @param InstagramConfig $config
     * @param Instagram $instagram
     * @param ImageProcessor $imageProcessor
     * @param InstagramLogger $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directoryList
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\Action $action
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        InstagramHelper $instagramHelper,
        InstagramConfig $config,
        Instagram $instagram,
        ImageProcessor $imageProcessor,
        InstagramLogger $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Product\Action $action
    )
    {
        $this->instagramHelper = $instagramHelper;
        $this->config = $config;
        $this->_instagram = $instagram;
        $this->imageProcessor = $imageProcessor;
        $this->logger = $logger;
        $this->_directoryList = $directoryList;

        $this->_account = $this->config->getAccountInformation();
        $this->messageManager = $context->getMessageManager();
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
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isObserverEnabled()) {
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $observer->getEvent()->getProduct();

        if (!$product->getData('posted_to_instagram')) {

            // Check and process exist image size
            $image = $this->imageProcessor->processBaseImage($product);

            // Start to Send Image to Instagram
            if ($image) {

                $caption = $this->instagramHelper->getInstagramPostDescription($product);

                if (!empty($this->_account)
                    && isset($this->_account['username'])
                    && isset($this->_account['password'])) {
                    $this->getInstagram()
                        ->setUser(
                            $this->_account['username'],
                            $this->_account['password']
                        );
                }

                try {

                    if (!$this->getInstagram()->login()) {
                        $this->messageManager->addErrorMessage(__('Unauthorized Instagram Account, check your user/password setting'));
                    }

                    $result = $this->getInstagram()->uploadPhoto($image, $caption);

                    if (empty($result)) {
                        $this->messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                    }

                    if ($result['status'] === Instagram::STATUS_FAIL) {
                        $this->logger->recordInstagramLog(
                            $product,
                            $result,
                            InstagramItem::TYPE_ERROR
                        );

                        $this->messageManager->addComplexErrorMessage(
                            'InstagramError',
                            [
                                'instagram_link' => 'https://help.instagram.com/1631821640426723'
                            ]
                        );
                    }

                    if ($result['status'] === Instagram::STATUS_OK) {
                        $this->logger->recordInstagramLog(
                            $product,
                            $result,
                            InstagramItem::TYPE_SUCCESS
                        );

                        $this->messageManager->addComplexSuccessMessage(
                            'InstagramSuccess',
                            [
                                'instagram_link' => 'https://www.instagram.com/p/' . $result['media']['code']
                            ]
                        );
                    }


                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                }
            }
        }

    }

    /**
     * @return Instagram
     */
    public function getInstagram(): Instagram
    {
        return $this->_instagram;
    }
}
