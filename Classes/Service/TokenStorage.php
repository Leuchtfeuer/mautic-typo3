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

namespace Leuchtfeuer\Mautic\Service;

use TYPO3\CMS\Core\Registry;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Persists Mautic OAuth tokens in the TYPO3 sys_registry instead of the
 * versioned config.yaml. The four fields stored here rotate at runtime
 * (token refresh, OAuth callback, admin reset) and must not end up in git.
 */
final class TokenStorage
{
    private const NAMESPACE = 'tx_mautic_oauth';

    /**
     * @var list<string>
     */
    private const STRING_KEYS = ['accessToken', 'accessTokenSecret', 'refreshToken'];

    private readonly Registry $registry;

    public function __construct(?Registry $registry = null)
    {
        $this->registry = $registry ?? GeneralUtility::makeInstance(Registry::class);
    }

    /**
     * Indicates whether tokens have ever been written to the registry.
     * Used by YamlConfiguration to fall back to legacy YAML values for
     * existing installations whose tokens have not yet been migrated.
     */
    public function hasTokens(): bool
    {
        $sentinel = new \stdClass();
        foreach (['accessToken', 'accessTokenSecret', 'refreshToken', 'expires'] as $key) {
            if ($this->registry->get(self::NAMESPACE, $key, $sentinel) !== $sentinel) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return array{accessToken: string, accessTokenSecret: string, refreshToken: string, expires: int}
     */
    public function getTokens(): array
    {
        return [
            'accessToken' => (string)$this->registry->get(self::NAMESPACE, 'accessToken', ''),
            'accessTokenSecret' => (string)$this->registry->get(self::NAMESPACE, 'accessTokenSecret', ''),
            'refreshToken' => (string)$this->registry->get(self::NAMESPACE, 'refreshToken', ''),
            'expires' => (int)$this->registry->get(self::NAMESPACE, 'expires', 0),
        ];
    }

    /**
     * @param array<string, mixed> $tokens
     */
    public function saveTokens(array $tokens): void
    {
        foreach (self::STRING_KEYS as $key) {
            if (array_key_exists($key, $tokens)) {
                $this->registry->set(self::NAMESPACE, $key, (string)$tokens[$key]);
            }
        }
        if (array_key_exists('expires', $tokens)) {
            $this->registry->set(self::NAMESPACE, 'expires', (int)$tokens['expires']);
        }
    }
}
