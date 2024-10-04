<?php
/* * *******************************************************************************
 * The content of this file is subject to the ITS4YouSMTP license.
 * ("License"); You may not use this file except in compliance with the License
 * The Initial Developer of the Original Code is IT-Solutions4You s.r.o.
 * Portions created by IT-Solutions4You s.r.o. are Copyright(C) IT-Solutions4You s.r.o.
 * All Rights Reserved.
 * ****************************************************************************** */

class Core_Auth_Model extends Vtiger_Base_Model {

	protected $viewer;

	public static function getInstance()
	{
		return new self();
	}

	public function getProviderName() {
		return $_SESSION[$this->getModuleName()]['provider'];
	}

	public function getProvider()
	{
		$params = $this->getProviderParams();

		return new League\OAuth2\Client\Provider\Google($params);
	}

	public function getToken() {
		return $_SESSION[$this->getModuleName()]['token'];
	}

	public function setToken($value)
	{
		$_SESSION[$this->getModuleName()]['token'] = $value;
	}

	public function getProviderParams()
	{
		return [
			'clientId' => $this->getClientId(),
			'clientSecret' => $this->getClientSecret(),
			'redirectUri' => $this->getRedirectUri(),
			'accessType' => 'offline'
		];
	}

	public function getProviderOptions()
	{
		return [
			'scope' => ['openid email profile https://mail.google.com/']
		];
	}

	public function setProviderName($value)
	{
		$_SESSION[$this->getModuleName()]['provider'] = $value;
	}

	public function getRedirectUri()
	{
		return rtrim(vglobal('site_URL'), '/') . '/auth.php?provider=' . $this->getProviderName();
	}

	public function viewAuthForm() {

		$viewer = $this->getViewer();
		$viewer->assign('REDIRECT_URI', $this->getRedirectUri());
		$viewer->assign('CLIENT_SECRET', $this->getClientSecret());
		$viewer->assign('CLIENT_ID', $this->getClientId());
		$viewer->assign('PROVIDER', $this->getProviderName());
		$viewer->assign('TOKEN', $this->getToken());
		$viewer->view('AuthForm.tpl', $this->getModuleName());
	}

	public function getModuleName()
	{
		return 'Core';
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

	public function getClientId() {
		return $_SESSION[$this->getModuleName()]['client_id'];
	}

	public function setClientId($value)
	{
		$_SESSION[$this->getModuleName()]['client_id'] = $value;
	}

	public function getClientSecret() {
		return $_SESSION[$this->getModuleName()]['client_secret'];
	}

	public function setClientSecret($value)
	{
		$_SESSION[$this->getModuleName()]['client_secret'] = $value;
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
}