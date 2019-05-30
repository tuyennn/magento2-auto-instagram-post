<?php

namespace GhoSter\AutoInstagramPost\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface ItemSearchResultInterface extends SearchResultsInterface
{

    /**
     * @return \GhoSter\AutoInstagramPost\Api\Data\ItemInterface[]
     */
    public function getItems();

    /**
     * @param \GhoSter\AutoInstagramPost\Api\Data\ItemInterface[] $items
     * @return void
     */
    public function setItems(array $items);
}
