<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem\Driver\File as DriverFile;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

/**
 * Class ImageProcessor
 * @package GhoSter\AutoInstagramPost\Model
 */
class ImageProcessor
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
     * @var DirectoryList
     */
    protected $directoryList;

    /** @var DriverFile */
    private $driverFile;

    protected $productHasImage = false;

    /**
     * ImageProcessor constructor.
     *
     * @param Config $config
     * @param InstagramHelper $instagramHelper
     * @param DirectoryList $directoryList
     * @param DriverFile $driverFile
     */
    public function __construct(
        InstagramConfig $config,
        InstagramHelper $instagramHelper,
        DirectoryList $directoryList,
        DriverFile $driverFile
    ) {
        $this->config = $config;
        $this->instagramHelper = $instagramHelper;
        $this->directoryList = $directoryList;
        $this->driverFile = $driverFile;
    }

    /**
     * Get Base Image from Product
     *
     * @param $product Product
     * @return string
     * @throws FileSystemException
     */
    public function getBaseImage($product)
    {
        if ($product->getImage() !== 'no_selection') {
            $baseImage = $product->getImage();
        } else {
            $baseImage = $product->getSmallImage();
        }

        $this->productHasImage = $product->getImage() || $product->getSmallImage();

        if (!$baseImage) {
            return $this->config->getDefaultImage();
        }

        return $baseImage;
    }

    /**
     * Process Base Image
     *
     * @param $product Product
     * @return mixed|string|null
     * @throws FileSystemException
     */
    public function processBaseImage($product)
    {
        $baseImage = $this->getBaseImage($product);

        if ($this->productHasImage) {
            $baseDir = $this->directoryList->getPath('media') .
                DIRECTORY_SEPARATOR . 'catalog' .
                DIRECTORY_SEPARATOR . 'product';
        } else {
            $baseDir = '';
        }

        if ($baseImage) {
            $imageDir = $baseDir . $baseImage;

            if (strpos($baseImage, '.tmp') !== false) {
                $baseImage = str_replace('.tmp', '', $baseImage);
                $imageDir = str_replace('media', 'media' . DIRECTORY_SEPARATOR . 'tmp', $baseDir) . $baseImage;
            }

            if ($this->driverFile->isExists($imageDir)) {
                list($width, $height, $type, $attr) = getimagesize($imageDir);
                $imageSize = $width;
                if ($height > $width) {
                    $imageSize = $height;
                }
                if ($imageSize < 320) {
                    $imageSize = 800;
                }

                return $this->instagramHelper
                    ->getResizeImage(
                        $imageDir,
                        $baseImage,
                        $imageSize
                    );
            }
        }

        return null;
    }
}
