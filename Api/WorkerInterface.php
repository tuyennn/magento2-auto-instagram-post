<?php

namespace GhoSter\AutoInstagramPost\Api;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;

interface WorkerInterface
{

    /**
     * Post to Instagram by Product
     * @param Product $product
     */
    public function postInstagramByProduct(Product $product);

    /**
     * @param ProductCollection $collection
     * @return mixed
     */
    public function postInstagramByProductCollection(ProductCollection $collection);
}
