<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Manage;

use Magento\Catalog\Controller\Adminhtml\Product as ProductController;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Controller\Adminhtml\Product\Builder as ProductBuilder;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class Index
 */
class Index extends ProductController
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param ProductBuilder $productBuilder
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        ProductBuilder $productBuilder,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $productBuilder);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Event list page
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('GhoSter_AutoInstagramPost::manage_product');
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Posts By Product'));
        return $resultPage;
    }

    /**
     * Is the user allowed to view the blog post grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('GhoSter_AutoInstagramPost::manage_product');
    }
}
