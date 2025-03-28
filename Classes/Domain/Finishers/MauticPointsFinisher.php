<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Domain\Finishers;

/***
 *
 * This file is part of the "Mautic" extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 *  (c) 2023 Leuchtfeuer Digital Marketing <dev@leuchtfeuer.com>
 *
 ***/

use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Form\Domain\Finishers\AbstractFinisher;

class MauticPointsFinisher extends AbstractFinisher
{
    protected int $mauticId;

    protected object $contactRepository;

    public function __construct()
    {
        $this->contactRepository = GeneralUtility::makeInstance(ContactRepository::class);
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    /**
     * Adds or substracts points to a Mautic contact
     */
    #[\Override]
    protected function executeInternal()
    {
        $pointsModifier = (int)($this->parseOption('mauticPointsModifier') ?? 0);

        if ($this->mauticId === 0 || $pointsModifier === 0) {
            return;
        }

        $data = [];
        $data['eventName'] = $this->parseOption('mauticEventName') ?? '';

        $this->contactRepository->modifyContactPoints($this->mauticId, $pointsModifier, $data);
    }
}
