<?php

namespace GhoSter\AutoInstagramPost\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Escaper;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use GhoSter\AutoInstagramPost\Model\Config\Backend\Image as InstagramDefaultImage;
use GhoSter\AutoInstagramPost\Model\Serialize\Serializer;

/**
 * Class Config
 * @package GhoSter\AutoInstagramPost\Model
 */
class Config
{
    const XML_PATH_ENABLED_MODULE = 'auto_instagram_post/general/enabled';
    const XML_PATH_INSTAGRAM_USER = 'auto_instagram_post/general/username';
    const XML_PATH_INSTAGRAM_PASSWORD = 'auto_instagram_post/general/password';
    const XML_PATH_DEFAULT_IMAGE = 'auto_instagram_post/general/upload_image_id';
    const XML_PATH_ENABLE_OBSERVER = 'auto_instagram_post/general/enabled_observer';

    const XML_PATH_COMMENT_ENABLED = 'auto_instagram_post/comment_hashtag/enabled';
    const XML_PATH_COMMENT_PRODUCT_DESCRIPTION = 'auto_instagram_post/comment_hashtag/product_description';
    const XML_PATH_COMMENT_CATEGORY_HASHTAG = 'auto_instagram_post/comment_hashtag/category_hashtag';
    const XML_PATH_COMMENT_CUSTOM_HASHTAG_ENABLED = 'auto_instagram_post/comment_hashtag/enabled_custom';
    const XML_PATH_COMMENT_CUSTOM_HASHTAG = 'auto_instagram_post/comment_hashtag/hashtag';
    const XML_PATH_COMMENT_TEMPLATE = 'auto_instagram_post/comment_hashtag/description_template';

    const XML_PATH_CRON_ENABLED = 'auto_instagram_post/cron/enabled';
    const XML_PATH_CRON_TIME = 'auto_instagram_post/cron/time';
    const XML_PATH_CRON_FREQUENCY = 'auto_instagram_post/cron/frequency';
    const XML_PATH_CRON_LIMIT = 'auto_instagram_post/cron/limit';

    const SPACE_STRING = ' ';
    const DEFAULT_LIMIT_CRON = 10;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Config constructor.
     *
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param EncryptorInterface $encryptor
     * @param Escaper $escaper
     * @param DirectoryList $directoryList
     * @param Serializer $serialize
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        EncryptorInterface $encryptor,
        Escaper $escaper,
        DirectoryList $directoryList,
        Serializer $serializer
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->encryptor = $encryptor;
        $this->escaper = $escaper;
        $this->directoryList = $directoryList;
        $this->serializer = $serializer;
    }

    /**
     * Check if module enabled
     *
     * @param null|string|bool|int|Store $store
     * @return bool
     */
    public function isEnabled($store = null)
    {
        if (empty($this->getUsernameInfo($store))
            || empty($this->getPwdInfo($store))) {
            return false;
        }

        return $this->scopeConfig->isSetFlag(
            static::XML_PATH_ENABLED_MODULE,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Username
     *
     * @param null $store
     * @return mixed
     */
    public function getUsernameInfo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INSTAGRAM_USER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Password
     *
     * @param null $store
     * @return mixed
     */
    public function getPwdInfo($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_INSTAGRAM_PASSWORD,
            ScopeInterface::SCOPE_STORE,
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
        if (empty($this->getUsernameInfo($store))
            || empty($this->getPwdInfo($store))) {
            return null;
        }

        return [
            'username' => $this->getUsernameInfo($store),
            'password' => $this->getPwdInfo($store)
        ];
    }

    /**
     * Get Default Image URL Path
     *
     * @param null $store
     * @return string
     * @throws FileSystemException
     */
    public function getDefaultImage($store = null)
    {
        $uploadDir = InstagramDefaultImage::UPLOAD_DIR;

        $image = $this->scopeConfig->getValue(
            self::XML_PATH_DEFAULT_IMAGE,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if (!$image) {
            return '';
        }

        $imagePath = $this->directoryList->getPath('media')
            . DIRECTORY_SEPARATOR . $uploadDir
            . DIRECTORY_SEPARATOR . $image;

        return $imagePath;
    }

    /**
     * Auto Observer after product saved
     *
     * @param $store
     * @return bool
     */
    public function isObserverEnabled($store = null)
    {
        return (bool)$this->scopeConfig->getValue(
            self::XML_PATH_ENABLE_OBSERVER,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Hashtag was enabled
     *
     * @param null $store
     * @return bool
     */
    public function isEnableHashtag($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Hashtag follows Parent Category
     * @param null $store
     * @return bool
     */
    public function isEnableCategoryHashtag($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENT_CATEGORY_HASHTAG,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Custom Hashtag was enabled
     *
     * @param null $store
     * @return bool
     */
    public function isEnableCustomHashtag($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENT_CUSTOM_HASHTAG_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Check if Caption included Product Desc
     *
     * @param null $store
     * @return bool
     */
    public function isEnableProductDescription($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_COMMENT_PRODUCT_DESCRIPTION,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * Get Custom Tags
     *
     * @param null $store
     * @return array
     */
    public function getCustomHashHashtags($store = null)
    {
        $hashTagsStripped = [];

        $hashTagConfig = $this->scopeConfig->getValue(
            self::XML_PATH_COMMENT_CUSTOM_HASHTAG,
            ScopeInterface::SCOPE_STORE,
            $store
        );

        if ($hashTagConfig && $this->isEnableHashtag() && $this->isEnableCustomHashtag()) {
            $hashTags = $this->serializer->unserialize($hashTagConfig);

            if (is_array($hashTags)) {
                foreach ($hashTags as $key => $hashTag) {
                    $hashTagsStripped[$key]['hashtag'] = strtolower(preg_replace('/\s+/', '', $hashTag['hashtag']));
                    $hashTagsStripped[$key]['status'] = $hashTag['status'];

                }
            }
        }

        return $hashTagsStripped;
    }

    /**
     * @param null $store
     * @return array|string
     */
    public function getInstagramPostTemplate($store = null)
    {
        return $this->escaper->escapeHtml($this->scopeConfig->getValue(
            self::XML_PATH_COMMENT_TEMPLATE,
            ScopeInterface::SCOPE_STORE,
            $store
        ));
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isCronEnabled($store = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_CRON_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return mixed
     */
    public function getCronTime()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CRON_TIME,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getCronFreq()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CRON_FREQUENCY,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return mixed
     */
    public function getCronLimit()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_CRON_LIMIT,
            ScopeInterface::SCOPE_STORE
        );
    }
}
