<?php
declare(strict_types=1);

namespace Abbott\MetabolicOrdering\Model;

use Abbott\MetabolicOrdering\Api\Data\MetabolicSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class MetabolicSearchResult extends SearchResults implements MetabolicSearchResultsInterface
{
}
