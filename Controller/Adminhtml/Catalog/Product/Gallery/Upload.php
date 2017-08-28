<?php

namespace GhoSter\AutoInstagramPost\Controller\Adminhtml\Catalog\Product\Gallery;

use Magento\Framework\App\Filesystem\DirectoryList;


class Upload extends \Magento\Catalog\Controller\Adminhtml\Product\Gallery\Upload {

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        try {

            $uploader = $this->_objectManager->create(
                'Magento\MediaStorage\Model\File\Uploader',
                ['fileId' => 'image']
            );
            $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
            /** @var \Magento\Framework\Image\Adapter\AdapterInterface $imageAdapter */
            $imageAdapter = $this->_objectManager->get('Magento\Framework\Image\AdapterFactory')->create();
            $uploader->addValidateCallback('catalog_product_image', $imageAdapter, 'validateUploadFile');
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            /** @var \Magento\Framework\Filesystem\Directory\Read $mediaDirectory */
            $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')
                ->getDirectoryRead(DirectoryList::MEDIA);
            $config = $this->_objectManager->get('Magento\Catalog\Model\Product\Media\Config');
            $result = $uploader->save($mediaDirectory->getAbsolutePath($config->getBaseTmpMediaPath()));

            list($width, $height, $type, $attr) = getimagesize($result['path'] . $result['file']);

            $this->_eventManager->dispatch(
                'catalog_product_gallery_upload_image_after',
                ['result' => $result, 'action' => $this]
            );

            unset($result['tmp_name']);

            unset($result['path']);

            $result['url'] = $this->_objectManager->get('Magento\Catalog\Model\Product\Media\Config')
                ->getTmpMediaUrl($result['file']);
            $result['file'] = $result['file'] . '.tmp';

            if($width < 320 || $height < 320) {
                $result = ['error' => __('Please upload larger image, minimum is 320 pixels. Current('.$width.'x'.$height.')'),  'errorcode' => __('Please upload larger image, minimum is 320 pixels. Current('.$width.'x'.$height.')')];
                /** @var \Magento\Framework\Controller\Result\Raw $response */
                $response = $this->resultRawFactory->create();
                $response->setHeader('Content-type', 'text/plain');
                $response->setContents(json_encode($result));
                return $response;
            }

        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }

        /** @var \Magento\Framework\Controller\Result\Raw $response */
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(json_encode($result));
        return $response;
    }

}