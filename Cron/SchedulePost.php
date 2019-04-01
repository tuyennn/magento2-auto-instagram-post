<?php

namespace GhoSter\AutoInstagramPost\Cron;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Instagram\Worker as InstagramWorker;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

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
     * SchedulePost constructor.
     * @param InstagramConfig $config
     * @param InstagramWorker $instagramWorker
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        InstagramConfig $config,
        InstagramWorker $instagramWorker,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->config = $config;
        $this->instagramWorker = $instagramWorker;
        $this->productCollection = $productCollectionFactory->create();
    }

    /**
     * Execute the cron
     */
    public function execute()
    {
        if(!$this->config->isEnabled()) {
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

            if($collection->count() > 0) {
                $this->instagramWorker
                    ->postInstagramByProductCollection($collection);
            }

        } catch (\Exception $e) {

        }

        return;
    }
}