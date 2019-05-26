<?php

namespace GhoSter\AutoInstagramPost\Plugin\Ui\DataProvider\Product;

use Magento\Framework\DB\Select;
use Magento\Framework\Api\Filter;
use GhoSter\AutoInstagramPost\Ui\DataProvider\Product\DataProvider;

/**
 * Class DataProviderPlugin
 * @package GhoSter\AutoInstagramPost\Plugin\Ui\DataProvider\Product
 */
class DataProviderPlugin
{
    /**
     * @param DataProvider $subject
     * @param callable $proceed
     * @param Filter $filter
     * @throws \Zend_Db_Select_Exception
     */
    public function aroundAddFilter(
        DataProvider $subject,
        callable $proceed,
        Filter $filter
    ) {

        $proceed($filter);

        if ($filter->getField() == 'posted_to_instagram') {
            $collection = $subject->getCollection();
            $select = $collection->getSelect();
            $partsFrom = $select->getPart(Select::FROM);

            if (isset($partsFrom['at_posted_to_instagram'])) {
                if ($whereParts = $select->getPart(Select::WHERE)) {
                    foreach ($whereParts as $key => $wherePartItem) {
                        $whereParts[$key] = $this->_replaceFilterCondition($wherePartItem);
                    }
                    $select->setPart(Select::WHERE, $whereParts);
                }
            }
        }
    }

    /**
     * Replace table alias in condition string
     *
     * @param string|null $conditionString
     * @return string|null
     */
    protected function _replaceFilterCondition($conditionString)
    {
        if ($conditionString === null) {
            return null;
        }

        if ($conditionString == "(at_posted_to_instagram.value = '0')") {
            $conditionString = "(at_posted_to_instagram.value = '0' OR at_posted_to_instagram.value IS NULL)";
        }

        return $conditionString;
    }
}