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

namespace Bitmotion\Mautic\Mautic;

use Mautic\Auth\AuthInterface;

class OAuth implements AuthInterface
{
    protected string $baseUrl;

    public function __construct(protected \Mautic\Auth\AuthInterface $authorization, string $baseUrl, protected string $accesToken = '', protected string $authorizationMode = '')
    {
        $this->baseUrl = rtrim($baseUrl, '/');
    }

    public function __call($method, $arguments)
    {
        if (!is_callable([$this->authorization, $method])) {
            throw new \BadMethodCallException(sprintf('Method "%s" does not exist!', $method), 1530044605);
        }

        return call_user_func_array([$this->authorization, $method], $arguments);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * Check if current authorization is still valid
     *
     * @return bool
     */
    #[\Override]
    public function isAuthorized()
    {
        return $this->authorization->isAuthorized();
    }

    /**
     * Make a request to server
     *
     * @param string $url
     * @param string $method
     *
     * @return array
     */
    #[\Override]
    public function makeRequest($url, array $parameters = [], $method = 'GET', array $settings = [])
    {
        return $this->authorization->makeRequest($url, $parameters, $method, $settings);
    }
}
