<?php

namespace GhoSter\AutoInstagramPost\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use GhoSter\AutoInstagramPost\Api\Data\ItemInterface;

interface ItemRepositoryInterface
{

    /**
     * @param int $id
     * @return \GhoSter\AutoInstagramPost\Api\Data\ItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param \GhoSter\AutoInstagramPost\Api\Data\ItemInterface $item
     * @return \GhoSter\AutoInstagramPost\Api\Data\ItemInterface
     */
    public function save(ItemInterface $item);

    /**
     * @param \GhoSter\AutoInstagramPost\Api\Data\ItemInterface $item
     * @return void
     */
    public function delete(ItemInterface $item);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \GhoSter\AutoInstagramPost\Api\Data\ItemSearchResultInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}