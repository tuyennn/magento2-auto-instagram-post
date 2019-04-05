<?php

namespace GhoSter\AutoInstagramPost\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;

class Hashtag extends ArraySerialized
{
    public function beforeSave()
    {
        // For value validations
        $exceptions = $this->getValue();

        // Validations

        $this->setValue($exceptions);

        return parent::beforeSave();
    }
}