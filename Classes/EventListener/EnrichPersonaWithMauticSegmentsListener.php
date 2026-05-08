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

namespace Leuchtfeuer\Mautic\EventListener;

use Leuchtfeuer\MarketingAutomation\Event\EnrichPersonaEvent;
use Leuchtfeuer\Mautic\Domain\Repository\ContactRepository;
use Leuchtfeuer\Mautic\Domain\Repository\PersonaRepository;

final class EnrichPersonaWithMauticSegmentsListener
{
    private readonly int $mauticId;

    public function __construct(
        private readonly ContactRepository $contactRepository,
        private readonly PersonaRepository $personaRepository,
    ) {
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    public function __invoke(EnrichPersonaEvent $event): void
    {
        if ($this->mauticId === 0) {
            return;
        }

        $currentPersona = $event->getCurrentPersona();
        $persona = $event->getPersona();

        $isEmptyPersonaId = $currentPersona->getId() === 0;
        // @extensionScannerIgnoreLine
        $languageChanged = $currentPersona->getLanguage() !== $persona->getLanguage();
        if (!$isEmptyPersonaId && !$languageChanged) {
            return;
        }

        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            fn($segment): int => (int)$segment['id'],
            $segments,
        );
        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        $event->setPersona($persona->withId($personaId));
    }
}
