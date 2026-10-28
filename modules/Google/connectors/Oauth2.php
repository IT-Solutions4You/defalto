<?php
/*************************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
/**
 * This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
 *
 * Modifications and additions by IT-Solutions4You (ITS4YOU) are Copyright (c) IT-Solutions4You s.r.o.
 *
 * These contributions are licensed under the GNU AGPL v3 License.
 * See LICENSE-AGPLv3.txt for more details.
 */

require_once 'vtlib/Vtiger/Net/Client.php';

class Google_Oauth2_Connector
{
    protected const STATE_SESSION_KEY = 'google_oauth2_state';
    protected const STATE_TTL = 600;

    protected $service_provider = 'Google';

    protected $source_module;

    protected $user_id;

    protected $db;

    protected $table_name = 'vtiger_google_oauth2';

    protected $service_name;

    protected $client_id;

    protected $client_secret;

    protected $redirect_uri;

    protected $scope;

    protected $state;

    protected $response_type = 'code';

    protected $access_type = 'offline';

    protected $approval_prompt = 'force';

    protected $scopes = [
        'Contacts' => 'https://www.google.com/m8/feeds',
        'Calendar' => 'https://www.googleapis.com/auth/calendar',
    ];

    public $token;

    const OAUTH2_AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';

    const OAUTH2_TOKEN_URI = 'https://accounts.google.com/o/oauth2/token';

    const OAUTH2_REVOKE_URI = 'https://accounts.google.com/o/oauth2/revoke';

    public function __construct($module, $userId = false)
    {
        global $site_URL;
        $this->source_module = $module;
        if ($userId) {
            $this->user_id = $userId;
        }
        $this->service_name = $this->service_provider . $module;
        $this->client_id = Google_Config_Connector::$clientId;
        $this->client_secret = Google_Config_Connector::$clientSecret;
        $this->redirect_uri = Google_Config_Connector::getRedirectUrl();
        $this->scope = implode(' ', array_values($this->scopes));
    }

    public function getClientId()
    {
        return $this->client_id;
    }

    public function getClientSecret()
    {
        return $this->client_secret;
    }

    public function getRedirectUri()
    {
        return $this->redirect_uri;
    }

    public function getScope()
    {
        return $this->scope;
    }

    public function getAccessType()
    {
        return $this->access_type;
    }

    public function getApprovalPrompt()
    {
        return $this->approval_prompt;
    }

    public function getAccessToken()
    {
        return json_encode($this->token['access_token']);
    }

    protected function getAuthUrl()
    {
        $params = [
            'response_type=' . urlencode($this->response_type),
            'redirect_uri=' . urlencode($this->redirect_uri),
            'client_id=' . urlencode($this->client_id),
            'scope=' . urlencode($this->scope),
            'access_type=' . urlencode($this->access_type),
            'approval_prompt=' . urlencode($this->approval_prompt),
            'state=' . urlencode($this->state),
        ];
        $queryString = implode('&', $params);

        return self::OAUTH2_AUTH_URL . "?$queryString";
    }

    public function hasStoredToken()
    {
        if (!isset($this->user_id)) {
            $this->user_id = Users_Record_Model::getCurrentUserModel()->getId();
        }
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }
        $sql = 'SELECT 1 FROM ' . $this->table_name . ' WHERE userid = ? AND service = ?';
        $params = [$this->user_id, $this->service_name];
        $res = $this->db->pquery($sql, $params);
        $hasStoredToken = ($this->db->num_rows($res) > 0) ? true : false;

        return $hasStoredToken;
    }

    protected function createState(): void
    {
        $this->state = bin2hex(random_bytes(32));
        $pendingStates = $_SESSION[self::STATE_SESSION_KEY] ?? [];
        $activeStates = [];
        $currentTime = time();

        foreach ($pendingStates as $stateDetails) {
            $created = is_array($stateDetails) ? ($stateDetails['created'] ?? 0) : 0;

            if (is_int($created) && $created <= $currentTime && ($currentTime - $created) <= self::STATE_TTL) {
                $activeStates[] = $stateDetails;
            }
        }

        $activeStates[] = [
            'value' => $this->state,
            'created' => $currentTime,
        ];
        $_SESSION[self::STATE_SESSION_KEY] = $activeStates;
    }

    protected function validateState(string $state): void
    {
        $pendingStates = $_SESSION[self::STATE_SESSION_KEY] ?? [];
        $activeStates = [];
        $matchedState = null;
        $currentTime = time();

        foreach ($pendingStates as $stateDetails) {
            $expectedState = is_array($stateDetails) ? ($stateDetails['value'] ?? '') : '';
            $created = is_array($stateDetails) ? ($stateDetails['created'] ?? 0) : 0;
            $isActive = is_string($expectedState)
                && is_int($created)
                && $created <= $currentTime
                && ($currentTime - $created) <= self::STATE_TTL;

            if (!$isActive) {
                continue;
            }

            if ($state !== '' && hash_equals($expectedState, $state)) {
                $matchedState = $expectedState;
                continue;
            }

            $activeStates[] = $stateDetails;
        }

        if (empty($activeStates)) {
            unset($_SESSION[self::STATE_SESSION_KEY]);
        } else {
            $_SESSION[self::STATE_SESSION_KEY] = $activeStates;
        }

        if ($matchedState === null) {
            throw new Exception('Invalid OAuth state');
        }
    }

    protected function showConsentScreen()
    {
        header('Location: ' . $this->getAuthUrl());
    }

    protected function fireRequest($url, $headers, $params = [], $method = 'POST')
    {
        $httpClient = new Vtiger_Net_Client($url);
        if (php7_count($headers)) {
            $httpClient->setHeaders($headers);
        }
        switch ($method) {
            case 'POST':
                $response = $httpClient->doPost($params);
                break;
            case 'GET':
                $response = $httpClient->doGet($params);
                break;
        }

        return $response;
    }

    protected function exchangeCodeForToken($code)
    {
        $params = [
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret,
            'redirect_uri'  => $this->redirect_uri
        ];
        $response = $this->fireRequest(self::OAUTH2_TOKEN_URI, [], $params);

        return $response;
    }

    protected function storeToken($token)
    {
        if (!isset($this->user_id)) {
            $this->user_id = Users_Record_Model::getCurrentUserModel()->getId();
        }
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }
        $decodedToken = json_decode($token, true);
        $refresh_token = $decodedToken['refresh_token'];
        unset($decodedToken['refresh_token']);
        $decodedToken['created'] = time();
        $accessToken = json_encode($decodedToken);
        $modulesSupported = ['Contacts', 'Calendar'];
        foreach ($modulesSupported as $moduleName) {
            $params = [$this->service_provider . $moduleName, $accessToken, $refresh_token, $this->user_id];
            $sql = 'INSERT INTO ' . $this->table_name . ' VALUES (' . generateQuestionMarks($params) . ')';
            $this->db->pquery($sql, $params);
        }
    }

    protected function retreiveToken()
    {
        if (!$this->user_id) {
            $this->user_id = Users_Record_Model::getCurrentUserModel()->getId();
        }
        $query = 'SELECT access_token,refresh_token FROM ' . $this->table_name . ' WHERE userid=? AND service =?';
        $params = [$this->user_id, $this->service_name];
        $result = $this->db->pquery($query, $params);
        $data = $this->db->fetch_array($result);
        $decodedAccessToken = json_decode(decode_html($data['access_token']), true);
        $refreshToken = decode_html($data['refresh_token']);

        return [
            'access_token'  => $decodedAccessToken,
            'refresh_token' => $refreshToken
        ];
    }

    protected function setToken($token)
    {
        $this->token = $token;
    }

    public function isTokenExpired()
    {
        if (null == $this->token || null == $this->token['access_token']) {
            return true;
        }
        // If the token is set to expire in the next 30 seconds.
        $expired = ($this->token['access_token']['created']
                + ($this->token['access_token']['expires_in'] - 30)) < time();

        return $expired;
    }

    protected function updateAccessToken($accesstoken, $refreshtoken)
    {
        if (!isset($this->db)) {
            $this->db = PearDatabase::getInstance();
        }
        $sql = 'UPDATE ' . $this->table_name . ' SET access_token = ? WHERE refresh_token = ? AND service = ?';
        $params = [$accesstoken, $refreshtoken, $this->service_name];
        $this->db->pquery($sql, $params);
    }

    public function refreshToken()
    {
        if ($this->token['refresh_token'] == null) {
            throw new Exception('refresh token is null');
        }
        $params = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $this->token['refresh_token'],
            'client_id'     => $this->client_id,
            'client_secret' => $this->client_secret
        ];
        $encodedToken = $this->fireRequest(self::OAUTH2_TOKEN_URI, [], $params);
        $decodedToken = json_decode($encodedToken, true);
        if (!isset($decodedToken['error'])) {
            $decodedToken['created'] = time();
            $token['access_token'] = $decodedToken;
            $token['refresh_token'] = $this->token['refresh_token'];
            $this->updateAccessToken(json_encode($decodedToken), $token['refresh_token']);
            $this->setToken($token);
        }
    }

    public function authorize()
    {
        if ($this->hasStoredToken()) {
            $token = $this->retreiveToken();
            $this->setToken($token);
            if ($this->isTokenExpired()) {
                $this->refreshToken();
            }

            return $this;
        } else {
            if (!empty($_GET['service'])) {
                $this->validateState((string)($_GET['state'] ?? ''));

                if (!empty($_GET['code'])) {
                    $authCode = $_GET['code'];
                    $token = $this->exchangeCodeForToken($authCode);
                    $this->storeToken($token);
                    echo '<script>window.opener.sync();window.close();</script>';
                    exit;
                }

                echo '<script>window.close();</script>';
                exit;
            } else {
                $this->createState();
                $this->showConsentScreen();
            }
        }
    }
}
