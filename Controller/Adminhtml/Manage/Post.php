<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use Magento\Catalog\Model\ProductFactory;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Logger as InstagramLogger;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;

class Post extends Action
{
    /**
     * @var InstagramHelper
     */
    protected $helper;

    /** @var InstagramConfig */
    protected $config;

    /**
     * @var InstagramLogger
     */
    protected $logger;

    /**
     * @var array
     */
    protected $_account;

    /**
     * @var Instagram
     */
    protected $_instagram;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\ImageProcessor
     */
    protected $imageProcessor;


    /**
     * Post constructor.
     *
     * @param Action\Context $context
     * @param InstagramConfig $config
     * @param InstagramHelper $helper
     * @param ProductFactory $productFactory
     * @param Instagram $instagram
     * @param ImageProcessor $imageProcessor
     * @param InstagramLogger $logger
     */
    public function __construct(
        Action\Context $context,
        InstagramConfig $config,
        InstagramHelper $helper,
        ProductFactory $productFactory,
        Instagram $instagram,
        ImageProcessor $imageProcessor,
        InstagramLogger $logger
    )
    {
        $this->config = $config;
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->_instagram = $instagram;
        $this->imageProcessor = $imageProcessor;
        $this->logger = $logger;
        $this->_account = $this->config->getAccountInformation();
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            /** @var $product \Magento\Catalog\Model\Product */
            $product = $this->productFactory->create()->load($id);

            try {

                $image = $this->imageProcessor->processBaseImage($product);

                if ($image) {
                    $caption = $this->helper->getInstagramPostDescription($product);

                    if (!empty($this->_account)
                        && isset($this->_account['username'])
                        && isset($this->_account['password'])) {
                        $this->getInstagram()
                            ->setUser(
                                $this->_account['username'],
                                $this->_account['password']
                            );
                    }

                    if (!$this->getInstagram()->login()) {
                        $this->messageManager->addErrorMessage(__('Unauthorized Instagram Account, check your user/password setting'));
                    }

                    $result = $this->getInstagram()
                        ->uploadPhoto(
                            $image,
                            $caption
                        );

                    if (empty($result)) {
                        $this->messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                    }

                    if (empty($result)) {
                        $this->logger->recordInstagramLog(
                            $product,
                            [],
                            InstagramItem::TYPE_ERROR
                        );

                        $this->messageManager->addComplexErrorMessage(
                            'InstagramError',
                            [
                                'instagram_link' => 'https://help.instagram.com/1631821640426723'
                            ]
                        );
                    }

                    if (isset($result['status'])
                        && $result['status'] === Instagram::STATUS_OK
                    ) {
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
                }

                return $resultRedirect->setPath('*/*/');

            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while posting to Instagram.')
                );
            }

            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @return Instagram
     */
    public function getInstagram(): Instagram
    {
        return $this->_instagram;
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('GhoSter_AutoInstagramPost::auto_instagram_post');
    }
}