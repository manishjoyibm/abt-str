<?php

namespace Abbott\PowerbiExport\Ui\Component\Form\Element;

class DataProvider extends \Magento\Ui\Component\Form\Element\Input
{
  /**
   * Prepare component configuration
   *
   * @return void
   */
    public function prepare()
    {
        parent::prepare();
      
        $config = $this->getData('config');

        if (isset($config['dataScope'])) {
            $this->setData('config', (array)$config);
        }
    }
}
