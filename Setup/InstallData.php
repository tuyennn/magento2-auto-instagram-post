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
use Magento\Eav\Model\Entity\Attribute\Group;
use Magento\Eav\Model\Entity\Attribute\GroupFactory;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\TypeFactory;

class InstallData implements InstallDataInterface
{
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
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        AttributeFactory $attributeFactory,
        SetFactory $attributeSetFactory,
        GroupFactory $attributeGroupFactory,
        TypeFactory $typeFactory,
        AttributeManagement $attributeManagement
    )
    {
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
            'posted_to_instagram',
            [
                'type' => 'int',
                'backend' => '',
                'frontend' => '',
                'label' => 'Posted to Instagram',
                'input' => 'hidden',
                'class' => '',
                'source' => 'Magento\Eav\Model\Entity\Attribute\Source\Boolean',
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL,
                'visible' => true,
                'required' => false,
                'user_defined' => true,
                'searchable' => false,
                'filterable' => false,
                'comparable' => false,
                'visible_on_front' => false,
                'used_in_product_listing' => false,
                'used_for_sort_by' => false,
                'is_used_in_grid' => false,
                'is_filterable_in_grid' => false,
                'unique' => false,
                'apply_to' => '',
                'default' => 0
            ]
        );

        $eavSetup->updateAttribute(\Magento\Catalog\Model\Product::ENTITY, 'posted_to_instagram', 'is_visible', '0');

        $this->addAttributeToAllAttributeSets('posted_to_instagram', '');
    }

    public function addAttributeToAllAttributeSets( $attributeCode,  $attributeGroupCode)
    {
        /** @var Attribute $attribute */
        $entityType = $this->eavTypeFactory->create()->loadByCode(\Magento\Catalog\Model\Product::ENTITY);
        $attribute = $this->attributeFactory->create()->loadByCode($entityType->getId(), $attributeCode);

        if (!$attribute->getId()) {
            return false;
        }

        /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection $setCollection */
        $setCollection = $this->attributeSetFactory->create()->getCollection();
        $setCollection->addFieldToFilter('entity_type_id', $entityType->getId());

        /** @var Set $attributeSet */
        foreach ($setCollection as $attributeSet) {
            /** @var Group $group */
            $group = $this->attributeGroupFactory->create()->getCollection()
                ->addFieldToFilter('attribute_group_code', ['eq' => $attributeGroupCode])
                ->addFieldToFilter('attribute_set_id', ['eq' => $attributeSet->getId()])
                ->getFirstItem();

            $groupId = $group->getId() ?: $attributeSet->getDefaultGroupId();

            // Assign:
            $this->attributeManagement->assign(
                \Magento\Catalog\Model\Product::ENTITY,
                $attributeSet->getId(),
                $groupId,
                $attributeCode,
                $attributeSet->getCollection()->count() * 10
            );
        }

        return true;
    }
}