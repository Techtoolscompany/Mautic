<?php

declare(strict_types=1);

/*
 * @copyright   2019 Mautic Contributors. All rights reserved
 * @author      Mautic
 *
 * @link        http://mautic.org
 *
 * @license     GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

namespace MauticPlugin\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use kamermans\OAuth2\GrantType\ClientCredentials;
use kamermans\OAuth2\GrantType\GrantTypeInterface;
use kamermans\OAuth2\GrantType\PasswordCredentials;
use kamermans\OAuth2\GrantType\RefreshToken;
use kamermans\OAuth2\OAuth2Middleware;
use MauticPlugin\IntegrationsBundle\Auth\Provider\AuthProviderInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\AuthConfigInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\AuthCredentialsInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials\ClientCredentialsGrantInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials\PasswordCredentialsGrantInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials\ScopeInterface;
use MauticPlugin\IntegrationsBundle\Auth\Provider\Oauth2TwoLegged\Credentials\StateInterface;
use MauticPlugin\IntegrationsBundle\Exception\InvalidCredentialsException;
use MauticPlugin\IntegrationsBundle\Exception\PluginNotConfiguredException;

/**
 * Factory for building HTTP clients that will sign the requests with Oauth2 headers.
 * Based on Guzzle OAuth 2.0 Subscriber - kamermans/guzzle-oauth2-subscriber package
 * @see https://github.com/kamermans/guzzle-oauth2-subscriber
 */
class HttpFactory implements AuthProviderInterface
{
    const NAME = 'oauth2_two_legged';

    /**
     * @var PasswordCredentialsGrantInterface|ClientCredentialsGrantInterface
     */
    private $credentials;

    /**
     * @var AuthConfigInterface|ConfigInterface
     */
    private $config;

    /**
     * @var Client
     */
    private $reAuthClient;

    /**
     * Cache of initialized clients.
     *
     * @var Client[]
     */
    private $initializedClients = [];

    /**
     * @return string
     */
    public function getAuthType(): string
    {
        return self::NAME;
    }

    /**
     * @param PasswordCredentialsGrantInterface|ClientCredentialsGrantInterface|AuthCredentialsInterface $credentials
     * @param AuthConfigInterface|ConfigInterface                                                                     $config
     *
     * @return ClientInterface
     * @throws PluginNotConfiguredException
     * @throws InvalidCredentialsException
     */
    public function getClient(AuthCredentialsInterface $credentials, ?AuthConfigInterface $config = null): ClientInterface
    {
        if (!$this->credentialsAreValid($credentials)) {
            throw new InvalidCredentialsException(
                sprintf(
                    'Credentials must implement either the %s or %s interfaces',
                    PasswordCredentialsGrantInterface::class,
                    ClientCredentialsGrantInterface::class
                )
            );
        }

        if (!$this->credentialsAreConfigured($credentials)) {
            throw new PluginNotConfiguredException('Authorization URL, client ID or client secret is missing');
        }

        // Return cached initialized client if there is one.
        if (!empty($this->initializedClients[$credentials->getClientId()])) {
            return $this->initializedClients[$credentials->getClientId()];
        }

        $this->credentials = $credentials;
        $this->config      = $config;

        $this->initializedClients[$credentials->getClientId()] = new Client(
            [
                'handler' => $this->getStackHandler(),
                'auth'    => 'oauth',
            ]
        );

        return $this->initializedClients[$credentials->getClientId()];
    }

    /**
     * @param AuthCredentialsInterface $credentials
     *
     * @return bool
     */
    private function credentialsAreValid(AuthCredentialsInterface $credentials): bool
    {
        return $credentials instanceof PasswordCredentialsGrantInterface || $credentials instanceof ClientCredentialsGrantInterface;
    }

    /**
     * @param ClientCredentialsGrantInterface|PasswordCredentialsGrantInterface|AuthCredentialsInterface $credentials
     *
     * @return bool
     */
    private function credentialsAreConfigured(AuthCredentialsInterface $credentials): bool
    {
        if (empty($credentials->getAuthorizationUrl()) || empty($credentials->getClientId()) || empty($credentials->getClientSecret())) {
            return false;
        }

        if ($credentials instanceof PasswordCredentialsGrantInterface && (empty($credentials->getUsername()) || empty($credentials->getPassword()))) {
            return false;
        }

        return true;
    }

    /**
     * @return HandlerStack
     */
    private function getStackHandler(): HandlerStack
    {
        $reAuthConfig          = $this->getReAuthConfig();
        $accessTokenGrantType  = $this->getGrantType($reAuthConfig);
        $refreshTokenGrantType = new RefreshToken($this->getReAuthClient(), $reAuthConfig);
        $middleware            = new OAuth2Middleware($accessTokenGrantType, $refreshTokenGrantType);

        $this->configureMiddleware($middleware);

        $stack = HandlerStack::create();
        $stack->push($middleware);

        return $stack;
    }

    /**
     * @return ClientInterface
     */
    private function getReAuthClient(): ClientInterface
    {
        if ($this->reAuthClient) {
            return $this->reAuthClient;
        }

        $this->reAuthClient = new Client(
            [
                'base_uri' => $this->credentials->getAuthorizationUrl(),
            ]
        );

        return $this->reAuthClient;
    }

    /**
     * @return array
     */
    private function getReAuthConfig(): array
    {
        $config = [
            'client_id'     => $this->credentials->getClientId(),
            'client_secret' => $this->credentials->getClientSecret(),
        ];

        if ($this->credentials instanceof ScopeInterface) {
            $config['scope'] = $this->credentials->getScope();
        }

        if ($this->credentials instanceof StateInterface) {
            $config['state'] = $this->credentials->getState();
        }

        if ($this->credentials instanceof ClientCredentialsGrantInterface) {
            return $config;
        }

        $config['username'] = $this->credentials->getUsername();
        $config['password'] = $this->credentials->getPassword();

        return $config;
    }

    /**
     * @param array $config
     *
     * @return GrantTypeInterface
     */
    private function getGrantType(array $config): GrantTypeInterface
    {
        if ($this->credentials instanceof ClientCredentialsGrantInterface) {
            return new ClientCredentials($this->getReAuthClient(), $config);
        }

        return new PasswordCredentials($this->getReAuthClient(), $config);
    }

    /**
     * @param OAuth2Middleware $oauth
     */
    private function configureMiddleware(OAuth2Middleware $oauth): void
    {
        if (!$this->config) {
            return;
        }

        if ($clientCredentialsSigner = $this->config->getClientCredentialsSigner()) {
            $oauth->setClientCredentialsSigner($clientCredentialsSigner);
        }

        if ($accessTokenSigner = $this->config->getAccessTokenSigner()) {
            $oauth->setAccessTokenSigner($accessTokenSigner);
        }

        if ($tokenPersistence = $this->config->getAccessTokenPersistence()) {
            $oauth->setTokenPersistence($tokenPersistence);
        }
    }
}