<?php

namespace GhoSter\AutoInstagramPost\Model\Config\Backend;

use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\App\Config\ValueFactory as ConfigValueFactory;
use Magento\Cron\Model\Config\Source\Frequency as SourceCronFrequency;
use Magento\Framework\Exception\LocalizedException;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

/**
 * Config Value for Cron
 *
 * Class Cron
 */
class Cron extends ConfigValue
{
    const CRON_STRING_PATH = 'crontab/default/jobs/auto_instagram_post/schedule/cron_expr';
    const CRON_MODEL_PATH = 'crontab/default/jobs/auto_instagram_post/run/model';
    const XML_PATH_BACKUP_ENABLED = 'groups/cron/fields/enabled/value';
    const XML_PATH_BACKUP_TIME = 'groups/cron/fields/time/value';
    const XML_PATH_BACKUP_FREQUENCY = 'groups/cron/fields/frequency/value';

    /** @var InstagramConfig */
    protected $instagramConfig;

    /**
     * @var ConfigValueFactory
     */
    protected $_configValueFactory;

    /**
     * @var string
     */
    protected $_runModelPath = '';

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param ConfigValueFactory $configValueFactory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $configValueFactory;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * Cron settings after save
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave()
    {
        $enabled = $this->getData(self::XML_PATH_BACKUP_ENABLED);
        $time = $this->getData(self::XML_PATH_BACKUP_TIME);
        $frequency = $this->getData(self::XML_PATH_BACKUP_FREQUENCY);

        $frequencyWeekly = SourceCronFrequency::CRON_WEEKLY;
        $frequencyMonthly = SourceCronFrequency::CRON_MONTHLY;

        if ($enabled) {
            $cronExprArray = [
                (int)$time[1],
                (int)$time[0],
                $frequency == $frequencyMonthly ? '1' : '*',
                '*',
                $frequency == $frequencyWeekly ? '1' : '*',
            ];
            $cronExprString = join(' ', $cronExprArray);
        } else {
            $cronExprString = '';
        }

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();

        } catch (\Exception $e) {
            throw new LocalizedException(__('We can\'t save the Cron expression.'));
        }
        return parent::afterSave();
    }
}
