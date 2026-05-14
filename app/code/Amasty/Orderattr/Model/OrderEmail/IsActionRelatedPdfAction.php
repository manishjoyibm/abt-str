<?php

declare(strict_types=1);

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Custom Checkout Fields for Magento 2
 */

namespace Amasty\Orderattr\Model\OrderEmail;

use Magento\Framework\App\RequestInterface;

/**
 * Check is current action related to print pdf ro invoice, shipment, etc.
 */
class IsActionRelatedPdfAction
{
    /**
     * @var string[]
     */
    private $printActions = [
        'print',
        'pdfdocs',
        'pdfinvoices',
        'invoice',
        'pdfshipments',
        'shipment'
    ];

    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request, array $printActions = [])
    {
        $this->request = $request;
        $this->printActions = array_merge($this->printActions, $printActions);
    }

    public function execute(): bool
    {
        return in_array($this->request->getActionName(), $this->printActions);
    }
}
