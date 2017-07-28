<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace GhoSter\AutoInstagramPost\Setup;

use Magento\Framework\Setup\UninstallInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;

class Uninstall implements UninstallInterface
{

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Module uninstall code
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @param ModuleDataSetupInterface $dataSetup
     * @return void
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context,
        ModuleDataSetupInterface $dataSetup
    ) {
        $setup->startSetup();

        $connection = $setup->getConnection();

        $connection->dropTable($connection->getTableName('ghoster_instagram_auto_post'));

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $dataSetup]);

        /**
         * Remove attributes to the eav/attribute
         */

        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY,
            'posted_to_instagram');

        $setup->endSetup();
    }
}
