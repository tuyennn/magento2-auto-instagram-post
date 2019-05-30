<?php

namespace GhoSter\AutoInstagramPost\Setup;

use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Event\ManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * @var ManagerInterface
     */
    protected $eventManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ManagerInterface $eventManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        ManagerInterface $eventManager,
        LoggerInterface $logger
    ) {
        $this->eventManager = $eventManager;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        $installer = $setup;
        $installer->startSetup();

        /**
         * Create table 'ghoster_instagram_auto_post'
         */

        try {

            $tableGroup = $installer->getConnection()->newTable(
                $installer->getTable('ghoster_instagram_auto_post')
            )->addColumn(
                'id',
                Table::TYPE_INTEGER,
                null,
                [
                    'identity' => true,
                    'unsigned' => true,
                    'nullable' => false,
                    'primary' => true,
                ],
                'ID'
            )->addColumn(
                'product_id',
                Table::TYPE_INTEGER,
                null,
                [
                    'unsigned' => true,
                    'nullable' => false
                ],
                'Product ID'
            )->addColumn(
                'type',
                Table::TYPE_TEXT,
                40,
                [],
                'Address Type'
            )->addColumn(
                'messages',
                Table::TYPE_TEXT,
                null,
                [
                    'nullable' => false,
                    'default' => '',
                ],
                'Messages'
            )->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                [
                    'nullable' => false,
                    'default' => Table::TIMESTAMP_INIT,
                ],
                'Created At'
            )->addIndex(
                $installer->getIdxName('ghoster_instagram_auto_post', ['product_id']),
                ['product_id']
            )->addForeignKey(
                $installer->getFkName(
                    'ghoster_instagram_auto_post',
                    'product_id',
                    'catalog_product_entity',
                    'entity_id'
                ),
                'product_id',
                $installer->getTable('catalog_product_entity'),
                'entity_id',
                Table::ACTION_CASCADE
            );

            $installer->getConnection()->createTable($tableGroup);

        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        $this->eventManager->dispatch('ghoster_option_module_install');

        $installer->endSetup();
    }
}
