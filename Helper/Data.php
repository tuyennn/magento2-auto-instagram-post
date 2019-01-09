<?php

namespace GhoSter\AutoInstagramPost\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\Store;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\CategoryFactory;


class Data extends AbstractHelper
{

    const XML_PATH_ENABLED_MODULE = 'auto_instagram_post/general/enable';
    const XML_PATH_INSTAGRAM_USER = 'auto_instagram_post/general/username';
    const XML_PATH_INSTAGRAM_PASSWORD = 'auto_instagram_post/general/password';
    const XML_PATH_DEFAULT_IMAGE = 'auto_instagram_post/general/upload_image_id';

    const XML_PATH_ENABLE_HASHTAG_DESCRIPTION = 'auto_instagram_post/comment_hashtag/enable';
    const XML_PATH_ENABLE_PRODUCT_DESCRIPTION = 'auto_instagram_post/comment_hashtag/product_description';
    const XML_PATH_ENABLE_CATEGORY_HASHTAG = 'auto_instagram_post/comment_hashtag/category_hashtag';
    const XML_PATH_ENABLE_CUSTOM_HASHTAG = 'auto_instagram_post/comment_hashtag/enable_custom';
    const XML_PATH_CUSTOM_HASHTAG = 'auto_instagram_post/comment_hashtag/hashtag';
    const XML_PATH_DESCRIPTION_TEMPLATE = 'auto_instagram_post/comment_hashtag/description_template';
    const XML_INSTAGRAM_CHARACTER_LIMIT = 2200;
    const XML_INSTAGRAM_HASHTAG_LIMIT = 30;
    const XML_CATEGORY_HASHTAG_LIMIT = 10;
    const SPACE_STRING = ' ';


    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $_encryptor;

    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory_list;

    /**
     * @var \Magento\Framework\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    protected $_imageFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var CategoryFactory
     */
    protected $categoryFactory;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;

    protected $_serializer;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        Encryptor $encryptor,
        \Magento\Framework\Escaper $_escaper,
        ProductFactory $productFactory,
        CategoryFactory $categoryFactory,
        \GhoSter\AutoInstagramPost\Model\Serialize\Serializer $serializer
    )
    {
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->directory_list = $directory_list;
        $this->_filesystem = $filesystem;
        $this->_imageFactory = $imageFactory;
        $this->productFactory = $productFactory;
        $this->categoryFactory = $categoryFactory;
        $this->_escaper = $_escaper;
        $this->_serializer = $serializer;
        parent::__construct($context);
    }

    /**
     * How to resize Your image before upload to Instagram
     *
     * @param $imageDir
     * @param null $image
     * @param $width
     * @param null $height
     * @param int $quality
     * @return mixed|string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getResizeImage($imageDir, $image = null, $width, $height = null, $quality = 100)
    {

        if ($height) {
            $imageResized = $this->directory_list->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . 'product_resized' . DIRECTORY_SEPARATOR . $width . 'x' . $height . $image;
        } else {
            $imageResized = $this->directory_list->getPath('media') . DIRECTORY_SEPARATOR . 'catalog' . DIRECTORY_SEPARATOR . 'product' . DIRECTORY_SEPARATOR . 'product_resized' . DIRECTORY_SEPARATOR . $width . $image;
        }
        if (!file_exists($imageResized)):
            $imageObj = $this->_imageFactory->create();
            $imageObj->open($imageDir);
            $imageObj->constrainOnly(true);
            $imageObj->keepAspectRatio(true);
            $imageObj->keepFrame(true);
            $imageObj->backgroundColor(array(255, 255, 255));
            $imageObj->resize($width, $height);
            $imageObj->save($imageResized);
        endif;

        if (file_exists($imageResized)) {

            $imageExts = explode('.', $imageResized);
            if (strtolower($imageExts[count($imageExts) - 1]) == 'png') {
                $realPng = $imageResized;
                $imageResized = str_replace(array('.png', '.Png', '.pNg', '.pnG', '.PNg', '.PnG', '.pNG', '.PNG'), '.jpg', $imageResized);
                if (!file_exists($imageResized)) {
                    $jpgImage = imagecreatefrompng($realPng);
                    $bg = imagecreatetruecolor(imagesx($jpgImage), imagesy($jpgImage));
                    imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
                    imagealphablending($bg, TRUE);
                    imagecopy($bg, $jpgImage, 0, 0, 0, 0, imagesx($jpgImage), imagesy($jpgImage));
                    imagedestroy($jpgImage);
                    imagejpeg($bg, $imageResized, $quality);
                    imagedestroy($bg);
                }
            }

            return $imageResized;

        } else {
            return '';
        }
    }


    /**
     * Check if module enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */

    public function isModuleEnabled($store = null)
    {
        if (empty($this->getUsernameInfo($store)) || empty($this->getPwdInfo($store))) {
            return false;
        }

        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLED_MODULE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Account Information from Configuration
     *
     * @param null|string|bool|int|Store $store
     * @return array
     */
    public function getAccountInformation($store = null)
    {
        if (empty($this->getUsernameInfo($store)) || empty($this->getPwdInfo($store))) {
            return null;
        }

        return [
            'username' => $this->getUsernameInfo($store),
            'password' => $this->getPwdInfo($store)
        ];
    }

    public function getUsernameInfo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INSTAGRAM_USER,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
    }

    public function getPwdInfo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INSTAGRAM_PASSWORD,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);
    }

    /**
     * Get Default Image URL Path
     *
     * @param null $store
     * @return string
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function getDefaultImage($store = null)
    {
        $uploadDir = \GhoSter\AutoInstagramPost\Model\Config\Backend\Image::UPLOAD_DIR;

        $image = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_IMAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store);

        if (!$image) {
            return '';
        }

        $imagePath = $this->directory_list->getPath('media') . DIRECTORY_SEPARATOR . $uploadDir . DIRECTORY_SEPARATOR . $image;

        return $imagePath;
    }

    /**
     * Check if Hashtag was enabled
     *
     * @param null $store
     * @return mixed
     */
    public function isEnableHashtag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_HASHTAG_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }


    /**
     * Check if Custom Hashtag was enabled
     *
     * @param null $store
     * @return mixed
     */
    public function isEnableCustomHashtag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_CUSTOM_HASHTAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Hashtag follows Parent Category
     * @param null $store
     * @return mixed
     */
    public function isEnableCategoryHashtag($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_CATEGORY_HASHTAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Caption included Product Desc
     *
     * @param null $store
     * @return mixed
     */
    public function isEnableProductDescription($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_PRODUCT_DESCRIPTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Custom Tags
     *
     * @param null $store
     * @return string
     */
    public function getCustomHashHashtags($store = null)
    {
        $hashTagsStripped = [];
        $html = '';

        $hashTagConfig = $this->scopeConfig->getValue(
            self::XML_PATH_CUSTOM_HASHTAG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($hashTagConfig && $this->isEnableHashtag() && $this->isEnableCustomHashtag()) {
            $hashTags = $this->_serializer->unserialize($hashTagConfig);

            if (is_array($hashTags)) {
                foreach ($hashTags as $key => $hashTag) {
                    $hashTagsStripped[$key]['hashtag'] = strtolower(preg_replace('/\s+/', '', $hashTag['hashtag']));
                    $hashTagsStripped[$key]['status'] = $hashTag['status'];

                }
            }
        }

        if (!empty($hashTagsStripped)) {
            foreach ($hashTagsStripped as $hashTag) {
                if ($hashTag['status']) {
                    $html .= '#' . $hashTag['hashtag'] . self::SPACE_STRING;
                }
            }
        }

        return $html;
    }

    /**
     * Get Category Tags
     *
     * @param $_product \Magento\Catalog\Model\Product
     * @return string
     */
    public function getCategoriesHashtags($_product)
    {

        $hashTagsStripped = [];
        $html = '';

        if ($this->isEnableCategoryHashtag() && $this->isEnableHashtag()) {

            $categoryIds = $_product->getCategoryIds();
            $i = 1;
            if (count($categoryIds)) {
                foreach ($categoryIds as $categoryId) {
                    $_category = $this->categoryFactory->create()->load($categoryId);
                    $hashTagsStripped[] = strtolower(preg_replace('/\s+/', '', $_category->getName()));
                    if ($i++ == self::XML_CATEGORY_HASHTAG_LIMIT) break;
                }
            }
        }

        if (!empty($hashTagsStripped)) {
            foreach ($hashTagsStripped as $hashTag) {
                $html .= '#' . $hashTag . self::SPACE_STRING;
            }
        }

        return $html;
    }


    /**
     * @param $_product \Magento\Catalog\Model\Product
     * @return string
     */
    public function getProductDescription($_product)
    {
        return strip_tags($_product->getDescription());
    }

    /**
     * @param null $store
     * @return array|string
     */
    public function getInstagramPostTemplate($store = null)
    {
        return $this->_escaper->escapeHtml($this->scopeConfig->getValue(
            self::XML_PATH_DESCRIPTION_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ));
    }


    /**
     * Get Final Caption for Instagram Post
     *
     * @param $_product \Magento\Catalog\Model\Product
     * @return string
     */
    public function getInstagramPostDescription($_product)
    {
        $html = '';
        $stringTemplate = $this->getInstagramPostTemplate();

        if (preg_match_all("~\{\{\s*(.*?)\s*\}\}~", $stringTemplate, $matches)) {

            foreach ($matches[1] as $key => $match) {
                switch ($match) {
                    case 'CUSTOMHASTAG':
                        $html .= $this->getCustomHashHashtags();
                        break;
                    case 'CATEGORYHASHTAG':
                        $html .= self::SPACE_STRING . $this->getCategoriesHashtags($_product);
                        break;
                    case 'PRODUCTDESC':
                        $html .= self::SPACE_STRING . $this->getProductDescription($_product);
                        break;
                    case 'PRODUCTNAME':
                        $html .= self::SPACE_STRING . $_product->getName();
                        break;
                    case 'PRODUCTLINK':
                        $html .= self::SPACE_STRING . $_product->getProductUrl();
                        break;
                    default:
                        $html .= $_product->getName();
                        break;
                }
            }

        } else {
            $html .= $_product->getName();
        }

        return $html;
    }
}