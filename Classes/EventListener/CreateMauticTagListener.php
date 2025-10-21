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

use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Domain\Repository\TagRepository;
use TYPO3\CMS\Core\DataHandling\Event\AfterDatabaseOperationsEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Event listener to create tags in Mautic and synchronize them with TYPO3.
 * Replaces the deprecated hook: $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']
 */
final class CreateMauticTagListener
{
    public function __construct(
        private readonly TagRepository $tagRepository,
        private readonly YamlConfiguration $config
    ) {
    }

    public function __invoke(AfterDatabaseOperationsEvent $event): void
    {
        $dataHandler = $event->getDataHandler();
        $table = $event->getTable();
        $status = $event->getStatus();
        $fields = $event->getFields();
        $id = $event->getId();

        if ($status !== 'new' || $table !== 'tx_mautic_domain_model_tag' || empty($fields['title'])) {
            return;
        }

        // Create tag in Mautic by calling the tracking pixel endpoint
        // @extensionScannerIgnoreLine
        $url = sprintf('%s/mtracking.gif?tags=%s', $this->config->getBaseUrl(), $fields['title']);
        GeneralUtility::getUrl($url);

        // Synchronize tags to receive proper IDs from Mautic
        $this->tagRepository->synchronizeTags();

        // Update record UID to display edit-form after syncing the new tag with Mautic
        // This avoids errors when AUTO_INCREMENT value differs between TYPO3 and Mautic
        // (see issue https://github.com/mautic/mautic-typo3/issues/82)
        $newTag = $this->tagRepository->findTagByTitle($fields['title']);
        if (!empty($newTag) && $newTag['uid'] !== $dataHandler->substNEWwithIDs[$id]) {
            $dataHandler->substNEWwithIDs[$id] = $newTag['uid'];
        }
    }
}