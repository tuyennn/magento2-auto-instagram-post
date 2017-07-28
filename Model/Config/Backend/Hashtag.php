<?php

namespace GhoSter\AutoInstagramPost\Model\Config\Backend;

class Hashtag extends \Magento\Config\Model\Config\Backend\Serialized\ArraySerialized
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