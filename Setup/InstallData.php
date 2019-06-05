<?php

namespace GhoSter\AutoInstagramPost\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\AttributeManagement;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\TypeFactory;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{

    const GROUP_NAME = 'Auto Instagram Post';
    const ATTRIBUTE_CODE = 'posted_to_instagram';

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var TypeFactory
     */
    protected $eavTypeFactory;

    /**
     * @var GroupFactory
     */
    protected $attributeGroupFactory;

    /**
     * @var AttributeManagement
     */
    protected $attributeManagement;

    /**
     * InstallData constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param AttributeFactory $attributeFactory
     * @param SetFactory $attributeSetFactory
     * @param GroupFactory $attributeGroupFactory
     * @param TypeFactory $typeFactory
     * @param AttributeManagement $attributeManagement
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $attributeFactory,
        SetFactory $attributeSetFactory,
        GroupFactory $attributeGroupFactory,
        TypeFactory $typeFactory,
        AttributeManagement $attributeManagement
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->attributeFactory = $attributeFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->eavTypeFactory = $typeFactory;
        $this->attributeGroupFactory = $attributeGroupFactory;
        $this->attributeManagement = $attributeManagement;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);

        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            self::ATTRIBUTE_CODE,
            [
                'type' => 'int',
                'group' => self::GROUP_NAME,
                'backend' => \Magento\Catalog\Model\Product\Attribute\Backend\Boolean::class,
                'frontend' => '',
                'label' => 'Instagram Status',
                'input' => 'select',
                'class' => '',
                'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                'global' => Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => false,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'is_used_in_grid' => true,
                'is_filterable_in_grid' => true,
                'unique' => false,
                'apply_to' => '',
                'default' => 0
            ]
        );

        /** @var Attribute $attribute */
        $entityType = $this->eavTypeFactory->create()->loadByCode(\Magento\Catalog\Model\Product::ENTITY);
        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), self::ATTRIBUTE_CODE);

        if ($attribute->getId()) {

            $attributeSetIds = $eavSetup->getAllAttributeSetIds($entityType->getId());

            foreach ($attributeSetIds as $attributeSetId) {
                $eavSetup->addAttributeGroup($entityType->getId(), $attributeSetId, self::GROUP_NAME, 100);
                $attributeGroupId = $eavSetup->getAttributeGroupId(
                    $entityType->getId(),
                    $attributeSetId,
                    self::GROUP_NAME
                );
                // Add existing attribute to group
                $attributeId = $eavSetup->getAttributeId($entityType->getId(), self::ATTRIBUTE_CODE);
                $eavSetup->addAttributeToGroup(
                    $entityType->getId(),
                    $attributeSetId,
                    $attributeGroupId,
                    $attributeId,
                    null
                );
            }
        }
    }
}
