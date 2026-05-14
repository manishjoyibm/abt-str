<?php
namespace Abbott\AdditionalAttributes\Plugin;

use Magento\CatalogGraphQl\DataProvider\Product\LayeredNavigation\LayerBuilder as LB;

/*To chnage sort order of category attribute*/
class LayerBuilder
{
    public function afterBuild(LB $subject, $layers)
    {
        if (isset($layers[1]) && isset($layers[0])) {
            $layers = array_replace($layers, [$layers[1],$layers[0]]);
        }
        return $layers;
    }
}
