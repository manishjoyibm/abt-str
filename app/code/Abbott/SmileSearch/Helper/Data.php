<?php

namespace Abbott\SmileSearch\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Mirasvit\Misspell\Service\QueryService as QueryHelper;
use Mirasvit\Misspell\Adapter\Elasticsearch\Suggester;
use Mirasvit\Misspell\Model\ConfigProvider;

class Data extends AbstractHelper
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var QueryHelper
     */
    protected $queryHelper;

    protected $suggester;

    /**
     * Construct
     *
     * @param QueryHelper $queryHelper
     * @param Suggester $suggester
     * @param ConfigProvider $config
     */
    public function __construct(
        QueryHelper $queryHelper,
        Suggester $suggester,
        ConfigProvider $config
    ) {
        $this->config = $config;
        $this->queryHelper = $queryHelper;
        $this->suggester = $suggester;
    }

    /**
     * Spell Correction
     *
     * @param $searchTerm
     * @return false|string
     */
    public function spellCorrection($searchTerm)
    {
        if ($this->config->isMisspellEnabled()) {
            return $this->doSpellCorrection($searchTerm);
        }
        return false;
    }

    /**
     * DoSpellCorrection
     *
     * @param $searchTerm
     * @return false|string
     */
    public function doSpellCorrection($searchTerm)
    {
        $suggestedText = $this->suggester->suggest($searchTerm);
        if ($suggestedText
            && $suggestedText != $this->queryHelper->getMisspellText()
            && $this->queryHelper->getNumResults($suggestedText)
        ) {
            return $suggestedText;
        }
        return false;
    }
}
