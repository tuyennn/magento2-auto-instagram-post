<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\Product;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Instagram\Worker as InstagramWorker;

class Post extends Action
{
    /**
     * @var InstagramWorker
     */
    protected $instagramWorker;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * Post constructor.
     *
     * @param Action\Context $context
     * @param ProductFactory $productFactory
     * @param InstagramWorker $instagramWorker
     */
    public function __construct(
        Action\Context $context,
        ProductFactory $productFactory,
        InstagramWorker $instagramWorker
    ) {
        $this->productFactory = $productFactory;
        $this->instagramWorker = $instagramWorker;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('id');

        if ($id) {
            /** @var $product Product */
            $product = $this->productFactory->create()->load($id);

            try {

                $result = $this->instagramWorker->postInstagramByProduct($product);

                if ($result['status'] === Instagram::STATUS_FAIL) {
                    $this->messageManager->addComplexErrorMessage(
                        'InstagramError',
                        [
                            'instagram_link' => 'https://help.instagram.com/1631821640426723'
                        ]
                    );
                }

                if ($result['status'] === Instagram::STATUS_OK) {
                    $this->messageManager->addComplexSuccessMessage(
                        'InstagramSuccess',
                        [
                            'instagram_link' => 'https://www.instagram.com/p/' . $result['media']['code']
                        ]
                    );
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
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('GhoSter_AutoInstagramPost::auto_instagram_post');
    }
}