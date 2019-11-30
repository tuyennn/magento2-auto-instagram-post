<?php

namespace GhoSter\AutoInstagramPost\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

/**
 * Config Value for Hashtag
 *
 * Class Hashtag
 */
class Hashtag extends ArraySerialized
{
    public function beforeSave()
    {
        $exceptions = $this->getValue();
        $this->setValue($exceptions);

        return parent::beforeSave();
    }
}
