<?php

namespace GhoSter\AutoInstagramPost\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;


class PostStatusOptions extends Column
{

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['is_posted_to_instagram'] = isset($item[$this->getData('name')]) && $item[$this->getData('name')] ? true : false;
                $class = 'grid-severity-minor';
                $text = __('Unposted');

                if (isset($item[$this->getData('name')]) && $item[$this->getData('name')]) {
                    $class = 'grid-severity-notice';
                    $text = __('Posted');
                }

                $html = '<span class="' . $class . '"><span>' . $text . '</span></span>';
                $item[$this->getData('name')] = $html;
            }
        }

        return $dataSource;
    }
}
