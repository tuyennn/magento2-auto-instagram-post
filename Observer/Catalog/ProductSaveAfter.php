<?php

namespace GhoSter\AutoInstagramPost\Observer\Catalog;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Framework\Exception\FileSystemException;
use Magento\Catalog\Model\Product;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Instagram\Worker as InstagramWorker;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;

/**
 * Class ProductSaveAfter
 */
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
     * @var MessageManager
     */
    protected $messageManager;

    /** @var InstagramWorker */
    protected $instagramWorker;

    /**
     * ProductSaveAfter constructor.
     * @param ActionContext $context
     * @param InstagramHelper $instagramHelper
     * @param InstagramConfig $config
     * @param InstagramWorker $instagramWorker
     */
    public function __construct(
        ActionContext $context,
        InstagramHelper $instagramHelper,
        InstagramConfig $config,
        InstagramWorker $instagramWorker
    ) {
        $this->instagramHelper = $instagramHelper;
        $this->config = $config;
        $this->instagramWorker = $instagramWorker;
        $this->messageManager = $context->getMessageManager();
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @throws FileSystemException
     */
    public function execute(
        Observer $observer
    ) {
        if (!$this->config->isEnabled()) {
            return;
        }

        if (!$this->config->isObserverEnabled()) {
            return;
        }

        /** @var $product Product */
        $product = $observer->getEvent()->getProduct();

        if (!$product->getData('posted_to_instagram')) {

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

            } catch (\Exception $e) {
                $this->messageManager
                    ->addErrorMessage(
                        __('Something went wrong while uploading to Instagram.')
                    );
            }
        }
    }
}
