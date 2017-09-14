<?php

namespace GhoSter\AutoInstagramPost\Observer\Catalog;

use Magento\Framework\Event\ObserverInterface;
use GhoSter\AutoInstagramPost\Helper\Data as AutoPostHelper;
use GhoSter\AutoInstagramPost\Model\Instagram;

class ProductSaveAfter implements ObserverInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected $helper;

    private $logger;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    protected $account;

    protected $instagram;

    protected $directory_list;

    protected $_productRepositoryFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;


    /**
     * ProductSaveAfter constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \GhoSter\AutoInstagramPost\Helper\Data $helper
     * @param \GhoSter\AutoInstagramPost\Model\Instagram $instagram
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Filesystem\DirectoryList $directory_list
     * @param \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        AutoPostHelper $helper,
        Instagram $instagram,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper

    )
    {
        $this->helper = $helper;
        $this->instagram = $instagram;
        $this->logger = $logger;
        $this->directory_list = $directory_list;
        $this->_productRepositoryFactory = $productRepositoryFactory;
        $this->jsonHelper = $jsonHelper;

        $this->account = $this->helper->getAccountInformation();
        $this->messageManager = $context->getMessageManager();

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

        if (!$this->helper->isModuleEnabled()) {
            return;
        }

        /** @var $product \Magento\Catalog\Model\Product */
        /** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryFactory */
        $product = $observer->getProduct();

        $item = $this->_objectManager->create('GhoSter\AutoInstagramPost\Model\Item');

        if (!$product->getData('posted_to_instagram')) {
            $image = '';
            $baseDir = $this->directory_list->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';

            $account = $this->account;
            $instagram = $this->instagram;

            if ($product->getImage() !== 'no_selection') {
                $baseImage = $product->getImage();
            } elseif ($product->getSmallImage() !== 'no_selection') {
                $baseImage = $product->getSmallImage();
            } else {
                $baseImage = $product->getThumbnail();
            }

            if (!$baseImage) {
                $image = $this->helper->getDefaultImage();
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

                    $image = $this->helper->getResizeImage($imageDir, $baseImage, $imageSize);

                }
            }

            // Start to Send Image to Instagram
            if ($image != '') {

                $caption = $this->helper->getInstagramPostDescription($product);

                if (!empty($account)) {
                    $instagram->setUser($account['username'], $account['password']);
                }

                try {

                    $instagram->login();

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }

                try {

                    $result = $instagram->uploadPhoto($image, $caption);

                    if ($result['status'] === 'ok') {
                        $product->setData('posted_to_instagram', 1);

                        $productRepositoryFactory = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
                        $productRepositoryFactory->save($product);
                        $this->messageManager->addSuccessMessage(__('The product has been posted to https://www.instagram.com/p/' . $result['media']['code']));
                    }

                    if ($result['status'] === 'fail') {

                        $this->messageManager->addErrorMessage(__($result['message'] . '. Please check product\'s images again, https://help.instagram.com/1631821640426723'));
                    }


                    $item->setProductId($product->getId());
                    $item->setType('success');
                    $item->setMessages($this->jsonHelper->jsonEncode($result));
                    $item->setCreatedAt(date('Y-m-d h:i:s'));
                    $item->save();

                } catch (\Exception $e) {
                    $this->logger->critical($e->getMessage());
                }
            }
        }

    }
}
