<?php

namespace GhoSter\AutoInstagramPost\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection as CategoryCollection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{

    const DEFAULT_INSTAGRAM_CHARACTER_LIMIT = 2200;
    const DEFAULT_INSTAGRAM_HASHTAG_LIMIT = 30;
    const DEFAULT_CATEGORY_HASHTAG_LIMIT = 10;
    const SPACE_STRING = ' ';

    /** @var DirectoryList */
    protected $directoryList;

    /** @var Filesystem\Directory\WriteInterface */
    protected $mediaWriteDirectory;

    /** @var Filesystem */
    protected $filesystem;

    /** @var \Magento\Framework\Image\AdapterFactory */
    protected $imageFactory;

    /** @var ProductFactory */
    protected $productFactory;

    /**
     * @var CategoryCollectionFactory
     */
    protected $categoryCollectionFactory;

    /** @var InstagramConfig */
    protected $config;

    /** @var LoggerInterface */
    protected $logger;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     * @param ProductFactory $productFactory
     * @param CategoryCollectionFactory $categoryCollectionFactory
     * @param InstagramConfig $config
     * @param LoggerInterface $logger
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        ProductFactory $productFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        InstagramConfig $config,
        LoggerInterface $logger
    ) {
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->productFactory = $productFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->config = $config;
        $this->mediaWriteDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Resize befor uploading
     *
     * @param $imageDir
     * @param $image
     * @param $width
     * @param null $height
     * @param int $quality
     * @return bool|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getResizeImage(
        $imageDir,
        $image,
        $width,
        $height = null,
        $quality = 100
    ) {

        if ($height) {
            $imageResizedDir = $this->directoryList->getPath('media')
                . DIRECTORY_SEPARATOR . 'catalog'
                . DIRECTORY_SEPARATOR . 'product'
                . DIRECTORY_SEPARATOR . 'instagram_processed'
                . DIRECTORY_SEPARATOR . $width . 'x' . $height;
        } else {
            $imageResizedDir = $this->directoryList->getPath('media')
                . DIRECTORY_SEPARATOR . 'catalog'
                . DIRECTORY_SEPARATOR . 'product'
                . DIRECTORY_SEPARATOR . 'instagram_processed'
                . DIRECTORY_SEPARATOR . $width;
        }

        $imageResized = $imageResizedDir . $image;

        try {

            if (!file_exists($imageResized)) {
                $this->_generateInstagramImage(
                    $imageResized,
                    $imageDir,
                    $width,
                    $height
                );
            }

            if (file_exists($imageResized)) {
                $extOriginal = strtolower(pathinfo($image, PATHINFO_EXTENSION));

                if (!in_array($extOriginal, ['jpg', 'jpeg'])) {
                    $wrongImageFormat = $imageResized;

                    $imageResized = $imageResizedDir . str_replace($extOriginal, '.jpg', $image);

                    $this->_convertWrongFormatImage(
                        $imageResized,
                        $wrongImageFormat,
                        $extOriginal,
                        $quality
                    );
                }

                return $imageResized;
            }

            return false;

        } catch (\Exception $e) {
            $this->logger->critical($e);
            return false;
        }

    }

    /**
     * Generate Image
     *
     * @param $imageResizedPath
     * @param $imageDir
     * @param $width
     * @param $height
     */
    private function _generateInstagramImage(
        $imageResizedPath,
        $imageDir,
        $width,
        $height
    ) {
        try {
            /** @var $image \Magento\Framework\Image */
            $image = $this->imageFactory->create();
            $image->open($imageDir);
            $image->constrainOnly(true);
            $image->keepAspectRatio(true);
            $image->keepFrame(true);
            $image->backgroundColor(array(255, 255, 255));
            $image->resize($width, $height);
            $image->save($imageResizedPath);

        } catch (\Exception $e) {

        }

        return;

    }

    /**
     * Convert Non JPG format
     *
     * @param $convertedImage
     * @param $sourceImage
     * @param $imageExt
     * @param int $quality
     */
    private function _convertWrongFormatImage(
        $convertedImage,
        $sourceImage,
        $imageExt,
        $quality = 100
    ) {

        if (!file_exists($convertedImage)) {
            switch ($imageExt) {
                case 'jpg':
                case 'jpeg':
                    $jpgImage = imagecreatefromjpeg($sourceImage);
                    break;
                case 'png':
                    $jpgImage = imagecreatefrompng($sourceImage);
                    break;
                case 'gif':
                    $jpgImage = imagecreatefromgif($sourceImage);
                    break;
                case 'bmp':
                    $jpgImage = imagecreatefrombmp($sourceImage);
                    break;
            }
            if (isset($jpgImage)) {
                $trueColorImage = imagecreatetruecolor(imagesx($jpgImage), imagesy($jpgImage));
                imagefill($trueColorImage, 0, 0, imagecolorallocate($trueColorImage, 255, 255, 255));
                imagealphablending($trueColorImage, true);
                imagecopy($trueColorImage, $jpgImage, 0, 0, 0, 0, imagesx($jpgImage), imagesy($jpgImage));
                imagedestroy($jpgImage);
                imagejpeg($trueColorImage, $convertedImage, $quality);
                imagedestroy($trueColorImage);
            }

        }
    }

    /**
     * Get Category Tags
     *
     * @param $product Product
     * @return string
     */
    public function getCategoriesHashtagsHtml($product)
    {
        $html = '';

        $hashTagsStrippedData = [];

        if ($this->config->isEnableCategoryHashtag() && $this->config->isEnableHashtag()) {

            try {

                /** @var $collection CategoryCollection */
                $collection = $this->categoryCollectionFactory->create();
                $collection->addAttributeToFilter('entity_id', $product->getCategoryIds());
                $collection->addNameToResult();

                $i = 1;
                foreach ($collection as $category) {
                    $hashTagsStrippedData[] = strtolower(preg_replace('/\s+/', '', $category->getName()));
                    if ($i++ == self::DEFAULT_CATEGORY_HASHTAG_LIMIT) {
                        break;
                    }
                }

            } catch (\Exception $e) {

            }

        }

        if (!empty($hashTagsStrippedData)) {
            foreach ($hashTagsStrippedData as $hashTag) {
                $html .= '#' . $hashTag . self::SPACE_STRING;
            }
        }

        return $html;
    }


    /**
     * @param $product Product
     * @return string
     */
    public function getProductDescription($product)
    {
        return strip_tags($product->getDescription());
    }


    /**
     * Get Final Caption for Instagram Post
     *
     * @param $product Product
     * @return string
     */
    public function getInstagramPostDescription($product)
    {
        $html = '';
        $stringTemplate = $this->config->getInstagramPostTemplate();

        if (preg_match_all("~\{\{\s*(.*?)\s*\}\}~", $stringTemplate, $matches)) {
            if (isset($matches[1]) && is_array($matches[1])) {
                foreach ($matches[1] as $key => $match) {
                    $addOnSpace = $key !== 0 ? self::SPACE_STRING : '';
                    switch ($match) {
                        case 'CUSTOMHASTAG':
                            $html .= $addOnSpace . $this->getCustomHashHashtagsHtml();
                            break;
                        case 'CATEGORYHASHTAG':
                            $html .= $addOnSpace . $this->getCategoriesHashtagsHtml($product);
                            break;
                        case 'PRODUCTDESC':
                            $html .= $addOnSpace . $this->getProductDescription($product);
                            break;
                        case 'PRODUCTNAME':
                            $html .= $addOnSpace . $product->getName();
                            break;
                        case 'PRODUCTLINK':
                            $html .= $addOnSpace . $product->getProductUrl();
                            break;
                        default:
                            break;
                    }
                }
            }
        } else {
            $html .= $product->getName();
        }

        return $html;
    }

    /**
     * Get Custom Tags
     *
     * @param null $store
     * @return string
     */
    public function getCustomHashHashtagsHtml($store = null)
    {
        $html = '';

        $hashTagsStripped = $this->config->getCustomHashHashtags($store);

        if (!empty($hashTagsStripped)) {
            foreach ($hashTagsStripped as $hashTag) {
                if ($hashTag['status']) {
                    $html .= '#' . $hashTag['hashtag'] . self::SPACE_STRING;
                }
            }
        }

        return $html;
    }
}