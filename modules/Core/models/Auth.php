<?php
/*
 * This file is part of the IT-Solutions4You CRM Software.
 *
 * (c) IT-Solutions4You s.r.o [info@its4you.sk]
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

class Core_Auth_Model extends Vtiger_Base_Model
{

    protected $viewer;

    public function getClientId()
    {
        return $_SESSION[$this->getModuleName()]['client_id'];
    }

    public function getClientSecret()
    {
        return $_SESSION[$this->getModuleName()]['client_secret'];
    }

    public static function getInstance()
    {
        return new self();
    }

    public function getModuleName()
    {
        return 'Core';
    }

    public function getProvider()
    {
        $params = $this->getProviderParams();

        return new League\OAuth2\Client\Provider\Google($params);
    }

    public function getProviderName()
    {
        return $_SESSION[$this->getModuleName()]['provider'];
    }

    public function getProviderOptions()
    {
        return [
            'scope' => ['openid email profile https://mail.google.com/'],
        ];
    }

    public function getProviderParams()
    {
        return [
            'clientId' => $this->getClientId(),
            'clientSecret' => $this->getClientSecret(),
            'redirectUri' => $this->getRedirectUri(),
            'accessType' => 'offline',
        ];
    }

    public function getRedirectUri()
    {
        return rtrim(vglobal('site_URL'), '/') . '/auth.php?provider=' . $this->getProviderName();
    }

    public function getToken()
    {
        return $_SESSION[$this->getModuleName()]['token'];
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

    public function redirectToProvider()
    {
        $provider = $this->getProvider();
        $options = $this->getProviderOptions();
        $authUrl = $provider->getAuthorizationUrl($options);
        $_SESSION['oauth2state'] = $provider->getState();

        header('Location: ' . $authUrl);
    }

    /**
     * @throws \League\OAuth2\Client\Provider\Exception\IdentityProviderException
     */
    public function retrieveToken()
    {
        $provider = $this->getProvider();
        $accessToken = $provider->getAccessToken('authorization_code', ['code' => $_GET['code']]);
        $token = $accessToken->getRefreshToken();

        if (!empty($token)) {
            $this->setToken($token);
        }
    }

    public function setClientId($value)
    {
        $_SESSION[$this->getModuleName()]['client_id'] = $value;
    }

    public function setClientSecret($value)
    {
        $_SESSION[$this->getModuleName()]['client_secret'] = $value;
    }

    public function setProviderName($value)
    {
        $_SESSION[$this->getModuleName()]['provider'] = $value;
    }

    public function setToken($value)
    {
        $_SESSION[$this->getModuleName()]['token'] = $value;
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
        $viewer->view('AuthForm.tpl', $this->getModuleName());
    }
}