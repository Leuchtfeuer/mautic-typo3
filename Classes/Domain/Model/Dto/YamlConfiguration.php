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

namespace Leuchtfeuer\Mautic\Domain\Model\Dto;

use Leuchtfeuer\Mautic\Service\TokenStorage;
use Symfony\Component\Yaml\Yaml;
use TYPO3\CMS\Core\Configuration\Loader\YamlFileLoader;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class YamlConfiguration implements SingletonInterface
{
    public const OAUTH1_AUTHORIZATION_MODE = 'OAuth1a';

    /**
     * @var int
     */
    protected $authorize = 0;

    /**
     * @var string
     */
    protected $baseUrl = '';

    /**
     * @var string
     */
    protected $publicKey = '';

    /**
     * @var string
     */
    protected $secretKey = '';

    /**
     * @var string
     */
    protected $accessToken = '';

    /**
     * @var string
     */
    protected $accessTokenSecret = '';

    /**
     * @var bool
     */
    protected $tracking = false;

    protected array $configurationArray;

    /**
     * @var string
     */
    protected $configFileName = 'config.yaml';

    protected string $configPath;

    protected string $fileName;

    /**
     * @var string
     */
    protected $trackingScriptOverride = '';

    /**
     * @var string
     */
    protected $authorizeMode = '';

    /**
     * @var string
     */
    protected $refreshToken = '';

    /**
     * @var int
     */
    protected $expires = 0;

    /**
     * @var list<string>
     */
    private const TOKEN_KEYS = ['accessToken', 'accessTokenSecret', 'refreshToken', 'expires'];

    public function __construct()
    {
        $this->configPath = Environment::getConfigPath() . '/mautic';
        $this->fileName = $this->configPath . '/' . $this->configFileName;
        $this->configurationArray = $this->getMergedConfiguration();
        $this->applyConfigurationToProperties();
        $this->migrateLegacyTokensIfNeeded();
    }

    protected function getYamlConfiguration(): array
    {
        $loader = GeneralUtility::makeInstance(YamlFileLoader::class);

        try {
            return $loader->load(GeneralUtility::fixWindowsFilePath($this->fileName), YamlFileLoader::PROCESS_IMPORTS);
        } catch (\Exception) {
            return [];
        }
    }

    /**
     * @deprecated Use getYamlConfiguration() instead.
     */
    protected function getRawEmConfig(): array
    {
        trigger_error('Use getYamlConfiguration() instead.', E_USER_DEPRECATED);

        return $this->getYamlConfiguration();
    }

    public function save(array $configuration = []): void
    {
        if (!file_exists($this->fileName)) {
            GeneralUtility::mkdir_deep($this->configPath);
        }

        $tokens = [];
        $yamlData = $configuration;
        foreach (self::TOKEN_KEYS as $key) {
            if (array_key_exists($key, $yamlData)) {
                $tokens[$key] = $yamlData[$key];
                unset($yamlData[$key]);
            }
        }
        if ($tokens !== []) {
            GeneralUtility::makeInstance(TokenStorage::class)->saveTokens($tokens);
        }

        $yamlFileContents = Yaml::dump($yamlData, 99, 2);
        GeneralUtility::writeFile($this->fileName, $yamlFileContents);

        $this->reloadConfigurations();
    }

    public function reloadConfigurations(): void
    {
        $this->configurationArray = $this->getMergedConfiguration();
        $this->applyConfigurationToProperties();
    }

    protected function getMergedConfiguration(): array
    {
        $yaml = $this->getYamlConfiguration();
        $tokenStorage = GeneralUtility::makeInstance(TokenStorage::class);
        if ($tokenStorage->hasTokens()) {
            return array_replace($yaml, $tokenStorage->getTokens());
        }
        return $yaml;
    }

    private function applyConfigurationToProperties(): void
    {
        $extensionConfiguration = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['mautic'] ?? [];
        $settings = array_replace_recursive($this->configurationArray, $extensionConfiguration);

        foreach ($settings as $key => $value) {
            if (property_exists(self::class, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * On first construction after upgrade, copy any tokens still present in
     * the YAML over to the registry and rewrite the YAML without them. Gated
     * by TokenStorage::hasTokens() so it runs at most once per installation.
     */
    private function migrateLegacyTokensIfNeeded(): void
    {
        $tokenStorage = GeneralUtility::makeInstance(TokenStorage::class);
        if ($tokenStorage->hasTokens()) {
            return;
        }
        foreach (self::TOKEN_KEYS as $key) {
            if (!empty($this->configurationArray[$key])) {
                $this->save($this->configurationArray);
                return;
            }
        }
    }

    public function getAuthorize(): int
    {
        return (int)$this->authorize;
    }

    public function getBaseUrl(): string
    {
        // @extensionScannerIgnoreLine
        return (string)$this->baseUrl;
    }

    public function getPublicKey(): string
    {
        return (string)$this->publicKey;
    }

    public function getSecretKey(): string
    {
        return (string)$this->secretKey;
    }

    public function getAccessToken(): string
    {
        return (string)$this->accessToken;
    }

    public function getAccessTokenSecret(): string
    {
        return (string)$this->accessTokenSecret;
    }

    public function isTracking(): bool
    {
        return (bool)$this->tracking;
    }

    public function getTrackingScriptOverride(): string
    {
        return (string)$this->trackingScriptOverride;
    }

    public function getConfigurationArray(): array
    {
        return $this->configurationArray;
    }

    public function getAuthorizeMode(): string
    {
        return empty($this->authorizeMode) ? self::OAUTH1_AUTHORIZATION_MODE : $this->authorizeMode;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function getExpires(): int
    {
        return (int)$this->expires;
    }

    public function isSameCredentials(array $configuration): bool
    {
        // extensionScannerIgnoreLine won't work if every && is on its own line
        // @extensionScannerIgnoreLine
        return $this->authorizeMode === $configuration['authorizeMode'] && $this->secretKey === $configuration['secretKey'] && $this->publicKey === $configuration['publicKey'] && $this->baseUrl === $configuration['baseUrl'];
    }

    public function isOAuth1(): bool
    {
        return $this->getAuthorizeMode() === self::OAUTH1_AUTHORIZATION_MODE;
    }
}
