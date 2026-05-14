<?php

declare(strict_types=1);

namespace Abbott\KountCustomFields\Plugin\Model\Ris;

use Exception;
use Kount_Ris_Request;
use Kount_Ris_Request_Inquiry;
use Psr\Log\LoggerInterface;

class Sender
{
    private const DEFAULT_ANID = '0123456789';

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * Construct Function
     *
     * @param LoggerInterface $logger
     */
    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * Setting default ANID in-case telephone number is blank
     *
     * @param \Kount\Kount360\Model\Ris\Sender $subject
     * @param Kount_Ris_Request $request
     * @return mixed[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSend(
        \Kount\Kount360\Model\Ris\Sender $subject,
        Kount_Ris_Request $request
    ): array {
        if ($request instanceof Kount_Ris_Request_Inquiry) {
            try {
                $phone = $request->getParm('ANID');
                if (empty($phone)) {
                    $phone = self::DEFAULT_ANID;
                    $request->setAnid($phone);
                }
            } catch (Exception $e) {
                $this->logger->error('Exception while setting ANID in RIS request: ' . $e->getMessage());
            }
        }

        return [$request];
    }
}
