<?php

namespace GhoSter\AutoInstagramPost\Cron;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use GhoSter\AutoInstagramPost\Model\Instagram\Worker as InstagramWorker;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use Psr\Log\LoggerInterface;

/**
 * Class SchedulePost
 * @package GhoSter\AutoInstagramPost\Cron
 */
class SchedulePost
{

    /**
     * @var InstagramConfig
     */
    protected $config;

    /**
     * @var InstagramWorker
     */
    protected $instagramWorker;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var LoggerInterface
     */
    private $logger;


    /**
     * SchedulePost constructor.
     * @param InstagramConfig $config
     * @param InstagramWorker $instagramWorker
     * @param ProductCollectionFactory $productCollectionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        InstagramConfig $config,
        InstagramWorker $instagramWorker,
        ProductCollectionFactory $productCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->instagramWorker = $instagramWorker;
        $this->productCollection = $productCollectionFactory->create();
        $this->logger = $logger;
    }

    /**
     * Execute the cron
     */
    public function execute()
    {
        if (!$this->config->isEnabled() || !$this->config->isCronEnabled()) {
            return;
        }

        $limit = $this->config->getCronLimit();

        if (!$limit) {
            $limit = InstagramConfig::DEFAULT_LIMIT_CRON;
        }

        try {

            $collection = $this->productCollection
                ->addAttributeToSelect('*')
                ->addAttributeToFilter(
                    'posted_to_instagram',
                    [
                        'or' => [
                            0 => ['eq' => 0],
                            1 => ['is' => new \Zend_Db_Expr('NULL')],
                        ]
                    ],
                    'left'
                );

            $collection->getSelect()->limit($limit);

            if ($collection->count() > 0) {
                $this->instagramWorker
                    ->postInstagramByProductCollection($collection);
            }

        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }
}