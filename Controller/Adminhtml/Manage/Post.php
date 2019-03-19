<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use Magento\Catalog\Model\ProductFactory;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Logger;

class Post extends Action
{
    /**
     * @var \GhoSter\AutoInstagramPost\Helper\Data
     */
    protected $helper;

    /**
     * @var Logger
     */
    protected $_logger;

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
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var \GhoSter\AutoInstagramPost\Model\ImageProcessor
     */
    protected $imageProcessor;


    public function __construct(
        Action\Context $context,
        \GhoSter\AutoInstagramPost\Helper\Data $helper,
        ProductFactory $productFactory,
        Instagram $instagram,
        ImageProcessor $imageProcessor,
        Logger $logger
    )
    {
        $this->helper = $helper;
        $this->productFactory = $productFactory;
        $this->_instagram = $instagram;
        $this->imageProcessor = $imageProcessor;
        $this->_logger = $logger;
        $this->_account = $this->helper->getAccountInformation();
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
                        $this->_getInstagram()
                            ->setUser(
                                $this->_account['username'],
                                $this->_account['password']
                            );
                    }

                    if (!$this->_getInstagram()->login()) {
                        $this->messageManager->addErrorMessage(__('Unauthorized Instagram Account, check your user/password setting'));
                    }

                    $result = $this->_getInstagram()->uploadPhoto($image, $caption);

                    if (empty($result)) {
                        $this->messageManager->addErrorMessage(__('Something went wrong while uploading to Instagram.'));
                    }

                    if (empty($result)) {
                        $this->_logger->recordInstagramLog(
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
                        $this->_logger->recordInstagramLog(
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
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while posting to Instagram.'));
            }

            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
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