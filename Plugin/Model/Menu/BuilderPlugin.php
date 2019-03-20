<?php

namespace GhoSter\AutoInstagramPost\Plugin\Model\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\ItemFactory;
use GhoSter\AutoInstagramPost\Helper\Data as InstagramHelper;

class BuilderPlugin
{

    const MENU_MANAGE_ID = 'GhoSter_AutoInstagramPost::manage_product';

    /**
     * @var ItemFactory
     */
    private $menuItemFactory;

    /**
     * @var InstagramHelper
     */
    protected $helper;

    /**
     * BuilderPlugin constructor.
     *
     * @param ItemFactory $menuItemFactory
     * @param InstagramHelper $helper
     */
    public function __construct(
        ItemFactory $menuItemFactory,
        InstagramHelper $helper
    )
    {
        $this->helper = $helper;
        $this->menuItemFactory = $menuItemFactory;
    }

    /**
     * Remove Manage Admin Menu item while module was not enabled
     *
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     */
    public function afterGetResult(
        Builder $subject,
        Menu $menu
    )
    {
        if(!$this->helper->isModuleEnabled() && $menu->get(self::MENU_MANAGE_ID)) {
            $menu->remove(self::MENU_MANAGE_ID);
        }

        return $menu;
    }
}