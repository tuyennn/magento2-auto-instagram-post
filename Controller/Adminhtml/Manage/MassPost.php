<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage;

use Magento\Backend\App\Action;
use Magento\Framework\Exception\LocalizedException;
use GhoSter\AutoInstagramPost\Model\Instagram\Worker as InstagramWorker;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

/**
 * Class MassPost
 * @package GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage
 */
class MassPost extends Action
{
    /** @var InstagramConfig */
    protected $config;

    /**
     * @var InstagramWorker
     */
    protected $instagramWorker;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * MassActions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * MassPost constructor.
     *
     * @param Action\Context $context
     * @param ProductFactory $productFactory
     * @param InstagramWorker $instagramWorker
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        ProductFactory $productFactory,
        InstagramWorker $instagramWorker,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->productFactory = $productFactory;
        $this->instagramWorker = $instagramWorker;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->getRequest()->isPost()) {
            throw new NotFoundException(__('Page not found'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $productIds = $this->getRequest()->getParam('selected');

        if (!empty($productIds)) {
            $productCollection = $this->collectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('entity_id', ['in' => $productIds]);
            try {

                $result = $this->instagramWorker->postInstagramByProductCollection($productCollection);

                $productUploaded = count($result);
                $errorNumber = $productCollection->count() - $productUploaded;

                if ($errorNumber) {
                    $this->messageManager->addComplexErrorMessage(
                        'InstagramError',
                        [
                            'instagram_link' => 'https://help.instagram.com/1631821640426723'
                        ]
                    );
                }

                if ($productUploaded) {
                    $this->messageManager->addSuccessMessage(
                        __('A total of %1 images(s) have been uploaded to Instagram.', $productUploaded)
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
