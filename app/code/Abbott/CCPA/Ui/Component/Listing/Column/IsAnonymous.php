<?php

namespace Abbott\CCPA\Ui\Component\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;

class IsAnonymous extends Column
{
    /**
     * Prepare Data Source
     *
     * @param mixed[] $dataSource
     * @return mixed[]
     */
    public function prepareDataSource($dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item[$this->getData('name')]) {
                    if ($item[$this->getData('name')][0]) {
                        $item[$this->getData('name')] = 'Yes';
                    } else {
                        $item[$this->getData('name')] = 'No';
                    }
                } else {
                    $item[$this->getData('name')] = 'No';
                }
            }
        }
        return $dataSource;
    }
}
