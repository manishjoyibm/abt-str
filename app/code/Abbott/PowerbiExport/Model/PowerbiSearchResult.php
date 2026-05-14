<?php
declare(strict_types=1);

namespace Abbott\PowerbiExport\Model;

use Abbott\PowerbiExport\Api\Data\PowerbiSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class PowerbiSearchResult extends SearchResults implements PowerbiSearchResultsInterface
{
}
