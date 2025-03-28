<?php

declare(strict_types=1);
namespace Bitmotion\Mautic\Slot;

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

use Bitmotion\MarketingAutomation\Dispatcher\SubscriberInterface;
use Bitmotion\MarketingAutomation\Persona\Persona;
use Bitmotion\Mautic\Domain\Repository\ContactRepository;
use Bitmotion\Mautic\Domain\Repository\PersonaRepository;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class MauticSubscriber implements SubscriberInterface, SingletonInterface
{
    protected int $mauticId;

    protected $languageNeedsUpdate = false;

    public function __construct(protected \Bitmotion\Mautic\Domain\Repository\ContactRepository $contactRepository, protected \Bitmotion\Mautic\Domain\Repository\PersonaRepository $personaRepository)
    {
        $this->mauticId = (int)($_COOKIE['mtc_id'] ?? 0);
    }

    #[\Override]
    public function needsUpdate(Persona $currentPersona, Persona $newPersona): bool
    {
        $isValidMauticId = !empty($this->mauticId);
        $isEmptyPersonaId = $currentPersona->getId() === 0;
        $this->languageNeedsUpdate = $isValidMauticId && $currentPersona->getLanguage() !== $newPersona->getLanguage();

        return $isValidMauticId && ($isEmptyPersonaId || $this->languageNeedsUpdate);
    }

    #[\Override]
    public function update(Persona $persona): Persona
    {
        $segments = $this->contactRepository->findContactSegments($this->mauticId);
        $segmentIds = array_map(
            fn($segment): int => (int)$segment['id'],
            $segments
        );
        $personaId = $this->personaRepository->findBySegments($segmentIds)['uid'] ?? 0;

        return $persona->withId($personaId);
    }

    public function setPreferredLocale($_, TypoScriptFrontendController $typoScriptFrontendController)
    {
        if ($this->languageNeedsUpdate) {
            $languageId = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('language', 'id');
            $site = GeneralUtility::makeInstance(SiteFinder::class)->getSiteByPageId($typoScriptFrontendController->id);
            $isoCode = $site->getLanguageById($languageId)->getTwoLetterIsoCode();

            $this->contactRepository->editContact(
                $this->mauticId,
                [
                    'preferred_locale' => $isoCode,
                ]
            );
        }
    }
}
