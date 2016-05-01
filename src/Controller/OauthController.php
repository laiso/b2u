<?php
namespace App\Controller;

use App\Controller\AppController;

use GuzzleHttp;
use League\OAuth2\Client\Token\AccessToken;

/**
 * Oauth Controller
 *
 * @property \App\Model\Table\OauthTable $Oauth
 */
class OauthController extends AppController
{
    /**
     * @return \Cake\Network\Response|null|string
     */
    public function authenticate()
    {
        $provider = getProvider();
        $code = $this->request->query['code'];
        if (!isset($code)) {
            $authorizationUrl = $provider->getAuthorizationUrl(['scope' => ['read_items']]);
            return $this->redirect($authorizationUrl);
        }

        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $code
        ]);
        
        $session = $this->request->session();
        $session->write('access_token', GuzzleHttp\json_encode($accessToken->jsonSerialize()));
        
        $request = $provider->getAuthenticatedRequest(
            'GET',
            'https://api.thebase.in/1/items',
            $accessToken
        );

        $client = new GuzzleHttp\Client();
        $response = $client->send($request);

        $this->response->body($response->getBody());
        return $this->response;
    }
}

function getProvider()
{
    $provider = new \League\OAuth2\Client\Provider\GenericProvider([
        'clientId'                => getenv('BASE_CLIENT_ID'),
        'clientSecret'            => getenv('BASE_CLIENT_SECRET'),
        'redirectUri'             => 'http://b2u.lai.so:8765/oauth',
        'urlAuthorize'            => 'https://api.thebase.in/1/oauth/authorize',
        'urlAccessToken'          => 'https://api.thebase.in/1/oauth/token',
        'urlResourceOwnerDetails' => 'https://api.thebase.in/'
    ]);
    return $provider;
}
