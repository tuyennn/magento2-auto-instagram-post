<?php

namespace GhoSter\AutoInstagramPost\Model\Instagram;

use GhoSter\AutoInstagramPost\Model\Logger as InstagramLogger;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use GhoSter\AutoInstagramPost\Api\WorkerInterface;
use GhoSter\AutoInstagramPost\Model\Instagram;
use GhoSter\AutoInstagramPost\Model\Item as InstagramItem;
use GhoSter\AutoInstagramPost\Model\ImageProcessor;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;
use Psr\Log\LoggerInterface;

/**
 * Class Worker
 * @package GhoSter\AutoInstagramPost\Model\Instagram
 */
class Worker implements WorkerInterface
{
    /**
     * @var InstagramConfig
     */
    protected $config;

    /**
     * @var InstagramHelper
     */
    protected $instagramHelper;

    /**
     * @var Instagram
     */
    protected $instagram;

    /**
     * @var InstagramLogger
     */
    protected $logger;

    /** @var LoggerInterface */
    protected $defaultLogger;

    /**
     * @var ImageProcessor
     */
    protected $imageProcessor;

    /**
     * @var array
     */
    protected $account = [];

    /**
     * @var bool
     */
    protected $isLoggedIn = false;

    /**
     * @var bool
     */
    protected $recordLog = true;

    /**
     * Worker constructor.
     *
     * @param InstagramConfig $config
     * @param InstagramHelper $instagramHelper
     * @param Instagram $instagram
     * @param InstagramLogger $logger
     * @param ImageProcessor $imageProcessor
     * @param LoggerInterface $defaultLogger
     */
    public function __construct(
        InstagramConfig $config,
        InstagramHelper $instagramHelper,
        Instagram $instagram,
        InstagramLogger $logger,
        ImageProcessor $imageProcessor,
        LoggerInterface $defaultLogger
    ) {
        $this->config = $config;
        $this->instagramHelper = $instagramHelper;
        $this->instagram = $instagram;
        $this->logger = $logger;
        $this->imageProcessor = $imageProcessor;
        $this->account = $this->config->getAccountInformation();
        $this->defaultLogger = $defaultLogger;

        $this->setInstagramUser();
        try {
            $this->loginInstagram();
        } catch (\Exception $e) {
            $this->defaultLogger->critical($e->getMessage());
        }
    }

    /**
     * Set Instagram User
     */
    public function setInstagramUser()
    {
        if (!empty($this->account)
            && isset($this->account['username'])
            && isset($this->account['password'])) {
            $this->getInstagram()
                ->setUser($this->account['username'], $this->account['password']);
        }
    }

    /**
     * @return Instagram
     */
    public function getInstagram(): Instagram
    {
        return $this->instagram;
    }

    /**
     * Do login
     *
     * @throws \Exception
     */
    public function loginInstagram()
    {
        if (!$this->isLoggedIn) {
            $this->isLoggedIn = $this->getInstagram()->login();
        }

        return $this->isLoggedIn;
    }

    /**
     *
     *
     * @param ProductCollection $collection
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function postInstagramByProductCollection(ProductCollection $collection)
    {
        $results = [];

        foreach ($collection as $product) {
            $result = $this->postInstagramByProduct($product);

            if (!empty($result)) {
                $results[] = $result;
            }
        }

        return $results;
    }

    /**
     *
     * Post to instagram by Product
     *
     * @param Product $product
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function postInstagramByProduct(Product $product)
    {
        $image = $this->imageProcessor->processBaseImage($product);

        if ($image
            && $this->isLoggedIn
        ) {
            try {
                $caption = $this->instagramHelper->getInstagramPostDescription($product);

                $result = $this->getInstagram()->uploadPhoto($image, $caption);

                if ($this->recordLog) {
                    $this->_recordLog($result, $product);
                }

                return $result;

            } catch (\Exception $e) {
                $this->defaultLogger->critical($e->getMessage());
            }
        }

        return [];
    }

    /**
     * @param $result
     * @param $product
     * @throws \Exception
     */
    private function _recordLog(&$result, $product)
    {
        if ($result['status'] === Instagram::STATUS_FAIL) {
            $this->logger->recordInstagramLog(
                $product,
                $result,
                InstagramItem::TYPE_ERROR
            );
        } elseif ($result['status'] === Instagram::STATUS_OK) {
            $this->logger->recordInstagramLog(
                $product,
                $result,
                InstagramItem::TYPE_SUCCESS
            );
        }
    }
}
