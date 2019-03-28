<?php

namespace GhoSter\AutoInstagramPost\Cron;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use GhoSter\AutoInstagramPost\Model\Logger as InstagramLogger;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;

class SchedulePost
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
     * @var array
     */
    protected $account;


    /**
     * @var Instagram
     */
    protected $instagram;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var ProductCollection
     */
    protected $productCollection;


    /**
     * SchedulePost constructor.
     * @param InstagramHelper $instagramHelper
     * @param InstagramConfig $config
     * @param Instagram $instagram
     * @param ImageProcessor $imageProcessor
     * @param InstagramLogger $logger
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        InstagramHelper $instagramHelper,
        InstagramConfig $config,
        Instagram $instagram,
        ImageProcessor $imageProcessor,
        InstagramLogger $logger,
        ProductCollectionFactory $productCollectionFactory
    )
    {
        $this->instagramHelper = $instagramHelper;
        $this->config = $config;
        $this->instagram = $instagram;
        $this->imageProcessor = $imageProcessor;
        $this->logger = $logger;
        $this->account = $this->config->getAccountInformation();
        $this->productCollection = $productCollectionFactory->create();
    }

    /**
     * Execute the cron
     */
    public function execute()
    {
        $limit = $this->config->getCronLimit();

        if (!$limit) {
            $limit = InstagramConfig::DEFAULT_LIMIT_CRON;
        }

        try {

            $collection = $this->productCollection
                ->addAttributeToFilter(
                    'posted_toinstagram',
                    [
                        'or' => [
                            0 => ['is' => 0],
                            1 => ['is' => new \Zend_Db_Expr('null')],
                        ]
                    ],
                    'left'
                );

            $collection->getSelect()->limit($limit);

            if (!empty($this->account)
                && isset($this->account['username'])
                && isset($this->account['password'])) {
                $this->getInstagram()
                    ->setUser(
                        $this->account['username'],
                        $this->account['password']
                    );
            }

            foreach ($collection as $product) {

                $image = $this->imageProcessor->processBaseImage($product);

                if ($image) {

                    $caption = $this->instagramHelper
                        ->getInstagramPostDescription($product);

                    try {

                        $result = $this->getInstagram()
                            ->uploadPhoto(
                                $image,
                                $caption
                            );

                        if (empty($result)) {
                            $this->logger->recordInstagramLog(
                                $product,
                                [],
                                InstagramItem::TYPE_ERROR
                            );
                        }

                        if ($result['status'] === Instagram::STATUS_FAIL) {
                            $this->logger->recordInstagramLog(
                                $product,
                                $result,
                                InstagramItem::TYPE_ERROR
                            );

                        }

                        if ($result['status'] === Instagram::STATUS_OK) {
                            $this->logger->recordInstagramLog(
                                $product,
                                $result,
                                InstagramItem::TYPE_SUCCESS
                            );

                        }


                    } catch (\Exception $e) {

                    }
                }
            }

        } catch (\Exception $e) {
        }

        return;
    }


    /**
     * @return Instagram
     */
    public function getInstagram(): Instagram
    {
        return $this->instagram;
    }
}