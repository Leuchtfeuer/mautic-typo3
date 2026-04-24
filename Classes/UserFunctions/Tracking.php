<?php

declare(strict_types=1);

/*
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * (c) Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 */

namespace Leuchtfeuer\Mautic\UserFunctions;

use Leuchtfeuer\Mautic\Service\MauticTrackingService;
use Psr\Http\Message\ServerRequestInterface;

final class Tracking
{
    public function __construct(private readonly MauticTrackingService $mauticTrackingService)
    {
    }

    /**
     * @param string $content
     * @param array<mixed> $conf
     * @param ServerRequestInterface $request
     */
    public function addMauticTrackingScript(string $content, array $conf, ServerRequestInterface $request): string
    {
        if (!$this->mauticTrackingService->isTrackingEnabled()) {
            return '';
        }
        return $this->mauticTrackingService->getTrackingCode();
    }
}
