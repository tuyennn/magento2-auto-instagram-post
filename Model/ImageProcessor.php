<?php

namespace GhoSter\AutoInstagramPost\Model;

class ImageProcessor
{

    /**
     * @var \GhoSter\AutoInstagramPost\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $_directoryList;

    protected $_image;


    public function __construct(
        \GhoSter\AutoInstagramPost\Helper\Data $helper,
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList

    )
    {
        $this->_helper = $helper;
        $this->_directoryList = $directoryList;
    }

    /**
     * Get Base Image from Product
     *
     * @param $product \Magento\Catalog\Model\Product
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

        if (!$baseImage) {
            return $this->_helper->getDefaultImage();
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
        $baseDir = $this->_directoryList->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product';

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

                return $this->_helper->getResizeImage($imageDir, $baseImage, $imageSize);
            }
        }

        return null;
    }
}