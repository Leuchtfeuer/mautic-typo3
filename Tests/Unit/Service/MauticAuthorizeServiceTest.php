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

namespace Leuchtfeuer\Mautic\Tests\Unit\Service;

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use Leuchtfeuer\Mautic\Domain\Model\Dto\YamlConfiguration;
use Leuchtfeuer\Mautic\Mautic\OAuth;
use Leuchtfeuer\Mautic\Service\MauticAuthorizeService;
use Mautic\Auth\OAuth as LibraryOAuth;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

final class MauticAuthorizeServiceTest extends UnitTestCase
{
    protected bool $resetSingletonInstances = true;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->createMock(LanguageService::class);
        $configuration = $this->createMock(YamlConfiguration::class);
        $configuration->method('getConfigurationArray')->willReturn([
            'baseUrl' => 'https://m.test',
            'publicKey' => 'public',
            'secretKey' => 'secret',
            'accessToken' => 'access',
            'refreshToken' => 'refresh',
            'expires' => 1,
        ]);
        GeneralUtility::setSingletonInstance(YamlConfiguration::class, $configuration);
    }

    public static function refreshFailureDataProvider(): \Generator
    {
        yield 'Mautic unreachable' => [
            new ConnectException('Connection timed out', new Request('POST', 'https://m.test/oauth/v2/token')),
            ConnectException::class,
            0,
        ];
        yield 'unexpected response containing a token' => [
            new \RuntimeException('Response: {"access_token":"leaked-token"', 500),
            \RuntimeException::class,
            500,
        ];
    }

    #[Test]
    #[DataProvider('refreshFailureDataProvider')]
    public function refreshAccessTokenLogsOnlyExceptionClassAndCodeAndReturnsFalse(
        \Exception $exception,
        string $expectedClass,
        int $expectedCode
    ): void {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(
            self::isString(),
            ['exception' => $expectedClass, 'code' => $expectedCode],
        );

        $subject = $this->createSubjectFailingWith($exception);
        $subject->setLogger($logger);

        self::assertFalse($subject->refreshAccessToken());
    }

    private function createSubjectFailingWith(\Exception $exception): MauticAuthorizeService
    {
        $libraryAuth = $this->createMock(LibraryOAuth::class);
        $libraryAuth->method('validateAccessToken')->willThrowException($exception);

        return new MauticAuthorizeService(new OAuth($libraryAuth, 'https://m.test'), false);
    }
}
