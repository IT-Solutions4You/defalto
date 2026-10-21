<?php
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * (c) IT-Solutions4You s.r.o
 *
 * This file is licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\Google;
use League\OAuth2\Client\Grant\RefreshToken;

class Core_Auth_Model extends Vtiger_Base_Model
{
    public const PROVIDER_GOOGLE = 'Google';
    public const PROVIDER_MICROSOFT = 'Microsoft';
    public const MICROSOFT_DEFAULT_TENANT = 'common';
    public const MICROSOFT_SCOPES = ['offline_access', 'https://outlook.office.com/IMAP.AccessAsUser.All', 'https://outlook.office.com/SMTP.Send'];

    protected $viewer;

    public function getAccessExpire()
    {
        return $this->getSession('client_access_expire');
    }

    public function getAccessToken()
    {
        return $this->getSession('client_access_token');
    }

    public function getClientId()
    {
        return $this->getSession('client_id');
    }

    public function getClientSecret()
    {
        return $this->getSession('client_secret');
    }

    /**
     * @param string $clientId
     * @param string $clientSecret
     * @param string $clientToken
     *
     * @return self
     */
    public static function getInstance(string $clientId = '', string $clientSecret = '', string $clientToken = ''): self
    {
        $instance = new self();

        if (!empty($clientId)) {
            $instance->setAuthClientId($clientId);
            $instance->setClientId($clientId);
            $instance->setClientSecret($clientSecret);
            $instance->setToken($clientToken);
        }

        return $instance;
    }

    public function getModuleName()
    {
        return 'Core';
    }

    public function getProvider()
    {
        return self::getProviderInstance((string)$this->getProviderName(), $this->getProviderParams());
    }

    /**
     * Returns the League OAuth2 provider for a provider name; Google is the default.
     *
     * @param string $providerName Core_Auth_Model::PROVIDER_* constant
     * @param array  $params       clientId, clientSecret and optionally redirectUri
     *
     * @return AbstractProvider
     */
    public static function getProviderInstance(string $providerName, array $params): AbstractProvider
    {
        if (self::PROVIDER_MICROSOFT === $providerName) {
            $tenant = rawurlencode(self::getMicrosoftTenant());

            return new GenericProvider(array_merge($params, [
                'urlAuthorize'            => sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/authorize', $tenant),
                'urlAccessToken'          => sprintf('https://login.microsoftonline.com/%s/oauth2/v2.0/token', $tenant),
                'urlResourceOwnerDetails' => 'https://graph.microsoft.com/oidc/userinfo',
                'scopes'                  => self::MICROSOFT_SCOPES,
                'scopeSeparator'          => ' ',
            ]));
        }

        return new Google($params);
    }

    /**
     * Returns the Microsoft Entra tenant used for Microsoft 365 OAuth: $microsoft_oauth_tenant
     * from config.inc.php (tenant ID or domain, needed for single-tenant app registrations),
     * otherwise 'common'.
     *
     * @return string
     */
    public static function getMicrosoftTenant(): string
    {
        $tenant = trim((string)vglobal('microsoft_oauth_tenant'));

        return '' !== $tenant ? $tenant : self::MICROSOFT_DEFAULT_TENANT;
    }

    /**
     * Returns the OAuth provider name for a mail server, or an empty string when the server
     * does not use OAuth (single place for all IMAP/SMTP consumers).
     *
     * @param string $server host name, optionally with protocol prefix and port
     *
     * @return string
     */
    public static function getProviderNameByServer(string $server): string
    {
        $server = strtolower($server);

        if (str_contains($server, 'gmail.com')) {
            return self::PROVIDER_GOOGLE;
        }

        if (str_contains($server, 'office365.com')) {
            return self::PROVIDER_MICROSOFT;
        }

        return '';
    }

    /**
     * Tells whether the mail server authenticates with an OAuth access token.
     *
     * @param string $server host name, optionally with protocol prefix and port
     *
     * @return bool
     */
    public static function isOAuthServer(string $server): bool
    {
        return '' !== self::getProviderNameByServer($server);
    }

    public function getProviderName()
    {
        return $this->getSession('provider');
    }

    public function getProviderOptions()
    {
        if (self::PROVIDER_MICROSOFT === $this->getProviderName()) {
            return [
                'scope' => self::MICROSOFT_SCOPES,
            ];
        }

        return [
            'scope' => ['openid email profile https://mail.google.com/'],
        ];
    }

    public function getProviderParams(): array
    {
        return [
            'clientId'     => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri'  => $this->getRedirectUri(),
            'accessType'   => 'offline',
        ];
    }

    /**
     * @return string
     * Use in getProviderParams: 'hostedDomain' => $this->getHostedDomain(),
     */
    public function getHostedDomain(): string
    {
        $siteUrl = parse_url(vglobal('site_URL'));

        return $siteUrl['host'];
    }

    /**
     * Returns the OAuth redirect URI; Microsoft does not accept query strings in redirect URIs
     * of personal accounts, auth.php reads the provider from the session anyway.
     *
     * @return string
     */
    public function getRedirectUri(): string
    {
        $url = rtrim(vglobal('site_URL'), '/') . '/auth.php';

        if (self::PROVIDER_MICROSOFT === $this->getProviderName()) {
            return $url;
        }

        return $url . '?provider=' . $this->getProviderName();
    }

    public function getSession($name)
    {
        return $_SESSION[$this->getModuleName()][$this->getSessionName()][$name];
    }

    public function getSessionName()
    {
        return md5($this->get('client_id'));
    }

    public function getToken()
    {
        return $this->getSession('token');
    }

    public function getViewer()
    {
        if (!empty($this->viewer)) {
            return $this->viewer;
        }

        $moduleName = $this->getModuleName();

        $viewer = new Vtiger_Viewer();
        $viewer->assign('MODULE', $moduleName);
        $viewer->assign('QUALIFIED_MODULE', $moduleName);

        $this->viewer = $viewer;

        return $viewer;
    }

    public function isExpired()
    {
        $expire = $this->getAccessExpire();

        return empty($expire) || time() > $expire;
    }

    public function redirectToProvider()
    {
        $provider = $this->getProvider();
        $options = $this->getProviderOptions();
        $authUrl = $provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $provider->getState();

        header('Location: ' . $authUrl);
    }

    public function retrieveAccessToken()
    {
        $provider = $this->getProvider();
        $grant = new RefreshToken();
        $accessToken = $provider->getAccessToken($grant, ['refresh_token' => $this->getToken()]);
        $this->setAccessToken($accessToken->getToken());
        $this->setAccessExpire($accessToken->getExpires());
    }

    public function retrieveToken(): void
    {
        $provider = $this->getProvider();
        $accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
        $token = $accessToken->getRefreshToken();

        if (!empty($token)) {
            $this->setToken($token);
        }

        $this->setAccessToken($accessToken->getToken());
        $this->setAccessExpire($accessToken->getExpires());
    }

    public function setAccessExpire($value)
    {
        $this->setSession('client_access_expire', $value);
    }

    public function setAccessToken($value)
    {
        $this->setSession('client_access_token', $value);
    }

    public function setClientId($value)
    {
        $this->setSession('client_id', $value);
    }

    public function setClientSecret($value)
    {
        $this->setSession('client_secret', $value);
    }

    public function setProviderName($value)
    {
        $this->setSession('provider', $value);
    }

    public function setSession($name, $value): void
    {
        $this->set($name, $value);

        if ($this->isEmpty('client_id')) {
            die('Empty client id');
        }

        $_SESSION[$this->getModuleName()][$this->getSessionName()][$name] = $value;
    }

    public function setToken($value)
    {
        $this->setSession('token', $value);
    }

    /**
     * @param object $recordModel
     *
     * @throws Exception
     */
    public function updateAccessToken(object $recordModel): void
    {
        if (!$this->isExpired()) {
            return;
        }

        $this->retrieveAccessToken();
        $accessToken = $this->getAccessToken();

        if (!empty($accessToken)) {
            $update = [
                'client_access_token' => $this->getAccessToken(),
            ];
            $search = [
                'client_id'     => $this->getClientId(),
                'client_secret' => $this->getClientSecret(),
            ];

            (new MailManager_Mailbox_Model())->getMailAccountTable()->updateData($update, $search);
            (new Settings_MailConverter_MailScannerInfo_Handler())->getMailScannerTable()->updateData($update, $search);
        }

        if (method_exists($recordModel, 'updateAccessToken')) {
            $recordModel->updateAccessToken($this);
        }
    }

    public function validateConfig()
    {
        if (empty($this->getProvider())) {
            die('Provider missing');
        }

        if (empty($this->getClientId())) {
            die('Client Id missing');
        }

        if (empty($this->getClientSecret())) {
            die('Client Secret missing');
        }
    }

    public function viewAuthForm()
    {
        $viewer = $this->getViewer();
        $viewer->assign('REDIRECT_URI', $this->getRedirectUri());
        $viewer->assign('CLIENT_SECRET', $this->getClientSecret());
        $viewer->assign('CLIENT_ID', $this->getClientId());
        $viewer->assign('PROVIDER', $this->getProviderName());
        $viewer->assign('TOKEN', $this->getToken());
        $viewer->assign('ACCESS_TOKEN', $this->getAccessToken());
        $viewer->assign('AUTHORIZATION_MESSAGE', $this->getAuthorizationMessage());
        $viewer->assign('EXPIRE_DATE', $this->getExpireDate());
        $viewer->view('AuthForm.tpl', $this->getModuleName());
    }

    public function retrieveAuthClientId()
    {
        $this->set('client_id', $_SESSION[$this->getModuleName()]['authClientId']);
    }

    public function setAuthClientId($value)
    {
        $_SESSION[$this->getModuleName()]['authClientId'] = $value;
    }

    /**
     * @return void
     */
    public function authorizationProcess(): void
    {
        if (!empty($this->getToken()) && !empty($this->getAccessToken()) && $this->isExpired()) {
            $this->retrieveAccessToken();
            $this->setAuthorizationMessage('Retrieved token by Client Token');
        }

        if (empty($_SESSION['oauth2state'])) {
            $this->redirectToProvider();
            $this->setAuthorizationMessage('Redirected');
        } elseif (empty($_GET['state']) || ($_GET['state'] !== $_SESSION['oauth2state'])) {
            $this->setAuthorizationMessage('Invalid state');
            unset($_SESSION['oauth2state']);
            unset($_SESSION['provider']);
        } elseif (empty($this->getToken()) || empty($this->getAccessToken())) {
            $this->retrieveToken();
            $this->setAuthorizationMessage('Retrieved Token by Authorization');
            unset($_SESSION['oauth2state']);
        } else {
            $this->setAuthorizationMessage('Retrieved Token from Session');
            unset($_SESSION['oauth2state']);
        }
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setAuthorizationMessage(string $value): void
    {
        $this->set('authorization_message', $value);
    }

    /**
     * @return string
     */
    public function getAuthorizationMessage(): string
    {
        return (string)$this->get('authorization_message');
    }

    public function setProviderByServer($server)
    {
        $providerName = self::getProviderNameByServer((string)$server);

        if (!empty($providerName)) {
            $this->setProviderName($providerName);
        }
    }

    public function getExpireDate()
    {
        $time = $this->getAccessExpire();
        $date = date('Y-m-d H:i:s', $time);
        $userDate = DateTimeField::convertToUserTimeZone($date);

        return DateTimeField::convertToUserFormat($userDate->format('Y-m-d H:i:s'));
    }

    public function retrieveLoggedUser()
    {
        if (empty($_SESSION['authenticated_user_id'])) {
            echo vtranslate('Required login to system', $this->getModuleName());
        }

        $recordModel = Users_Record_Model::getInstanceById($_SESSION['authenticated_user_id'], 'Users');

        vglobal('current_user', $recordModel->getEntity());
    }
}