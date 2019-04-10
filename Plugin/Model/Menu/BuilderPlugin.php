<?php

namespace GhoSter\AutoInstagramPost\Plugin\Model\Menu;

use Magento\Backend\Model\Menu\Builder;
use Magento\Backend\Model\Menu;
use GhoSter\AutoInstagramPost\Model\Config as InstagramConfig;

/**
 * Class BuilderPlugin
 * @package GhoSter\AutoInstagramPost\Plugin\Model\Menu
 */
class BuilderPlugin
{

    const MENU_MANAGE_ID = 'GhoSter_AutoInstagramPost::manage_product';

    /**
     * @var InstagramConfig
     */
    protected $config;

    /**
     * BuilderPlugin constructor.
     *
     * @param InstagramConfig $config
     */
    public function __construct(
        InstagramConfig $config
    ) {
        $this->config = $config;
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
    ) {
        if (!$this->config->isEnabled() && $menu->get(self::MENU_MANAGE_ID)) {
            $menu->remove(self::MENU_MANAGE_ID);
        }

        return $menu;
    }
}