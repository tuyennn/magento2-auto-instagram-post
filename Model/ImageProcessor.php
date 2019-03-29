<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Filesystem\DirectoryList;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

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

    protected $productHasImage = false;

    /**
     * ImageProcessor constructor.
     *
     * @param Config $config
     * @param InstagramHelper $instagramHelper
     * @param DirectoryList $directoryList
     */
    public function __construct(
        InstagramConfig $config,
        InstagramHelper $instagramHelper,
        DirectoryList $directoryList

    )
    {
        $this->config = $config;
        $this->instagramHelper = $instagramHelper;
        $this->directoryList = $directoryList;
    }

    /**
     * Get Base Image from Product
     *
     * @param $product Product
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
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
     * @param $product \Magento\Catalog\Model\Product
     * @return mixed|string|null
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function processBaseImage($product)
    {
        $baseImage = $this->getBaseImage($product);

        if($this->productHasImage) {
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

            if (file_exists($imageDir)) {
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