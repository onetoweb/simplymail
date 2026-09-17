<?php

namespace Onetoweb\Frama;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;
use Onetoweb\Frama\Config\Method;
use Onetoweb\Frama\Token;
use DateTime;
use Closure;

/**
 * Frama Api Client.
 * 
 * @author Jonathan van 't Ende <jvantende@onetoweb.nl>
 * @copyright Onetoweb. B.V.
 * 
 * @link https://developer.frama.nl/parcel/en/default.aspx
 */
class Client
{
    const VERSION = 3.0;
    
    /**
     * Base Urls.
     */
    public const BASE_URL_LIVE = 'https://restapi.simplymail.quadient.nl';
    public const BASE_URL_TEST = 'https://sandbox.simplymail.quadient.nl';
    
    /**
     * @var Token
     */
    private ?Token $token = null;
    
    /**
     * @var Closure|null
     */
    private ?Closure $tokenUpdateCallback = null;
    
    /**
     * @param string $username
     * @param string $password
     * @param bool $testModus = false
     * @param float $version = self::VERSION
     */
    public function __construct(
        
        #[\SensitiveParameter]
        private string $username,
        
        #[\SensitiveParameter]
        private string $password,
        
        private bool $testModus = false,
        private float $version = self::VERSION
    ) {
        $this->username = $username;
        $this->password = $password;
        $this->version = $version;
        $this->testModus = $testModus;
    }
    
    /**
     * @return void
     */
    public function requestToken(): void
    {
        $response = $this->request(Method::GET, 'login');
        
        // set expires
        $expires = new Datetime();
        $expires->setTimestamp(time() + $response['expiresIn']);
        
        // set token
        $token = new Token($response['accessToken'], $expires);
        $this->setToken($token);
        
        // token update callback
        ($this->updateTokenCallback)($this->getToken());
    }
    
    /**
     * @param Closure $updateTokenCallback
     */
    public function setUpdateTokenCallback(Closure $updateTokenCallback): void
    {
        $this->updateTokenCallback = $updateTokenCallback;
    }
    
    /**
     * @param Token $token
     *
     * @return void
     */
    public function setToken(Token $token): void
    {
        $this->token = $token;
    }
    
    /**
     * @return Token
     */
    public function getToken(): ?Token
    {
        return $this->token;
    }
    
    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->testModus ? self::BASE_URL_TEST : self::BASE_URL_LIVE;
    }
    
    /**
     * @param Method $method
     * @param string $endpoint
     * @param array $data = []
     * @param array $query = []
     * 
     * @return array|null
     */
    public function request(Method $method, string $endpoint, array $data = [], array $query = []): ?array
    {
        // build options
        $options = [
            RequestOptions::HTTP_ERRORS => false,
        ];
        
        // build request haders
        $headers = [
            'Cache-Control' => 'no-cache',
            'Connection' => 'close',
            'Accept' => 'application/json',
            'Api-Version' => $this->version
        ];
        
        // check token
        if ($endpoint != 'login') {
            
            if ($this->getToken() === null or $this->getToken()->isExpired()) {
                
                // request token
                $this->requestToken();
            }
            
            // add bearer token authorization header
            $headers['Authorization'] = "Bearer {$this->getToken()}";
            
        } else {
            
            // add authorization
            $options[RequestOptions::AUTH] = [
                $this->username,
                $this->password
            ];
        }
        
        //  add headers to request options
        $options[RequestOptions::HEADERS] = $headers;
        
        // add post data body
        if (count($data) > 0) {
            
            $options[RequestOptions::JSON] = $data;
            
        }
        
        // build query
        if (count($query) > 0) {
            $endpoint .= '?' . http_build_query($query);
        }
        
        // build guzzle client
        $guzzleClient = new GuzzleClient([
            'base_uri' => $this->getBaseUrl()
        ]);
        
        // build guzzle request
        $result = $guzzleClient->request($method->value, $endpoint, $options);
        
        // get contents
        $contents = $result->getBody()->getContents();
        
        // return data
        return json_decode($contents, true);
    }
    
    /**
     * @param array $data = []
     *
     * @return array|null
     */
    public function createShipments(array $data): ?array
    {
        return $this->request(Method::POST, 'parcel/shipment', $data);
    }
    
    /**
     * @param array $data = []
     *
     * @return array|null
     */
    public function deleteShipments(array $data): ?array
    {
        return $this->request(Method::DELETE, 'parcel/shipment', $data);
    }
    
    /**
     * @param array $data = []
     *
     * @return array|null
     */
    public function getLabels(array $data): ?array
    {
        return $this->request(Method::POST, '/parcel/label', $data);
    }
    
    /**
     * @param string $zipCode
     * @param string $countryCode = 'NL'
     * 
     * @return array|null
     */
    public function getPickupPoints(string $zipCode, string $countryCode = 'NL'): ?array
    {
        return $this->request(Method::GET, "/parcel/pickup/$countryCode/$zipCode");
    }
    
    /**
     * @param string $countryCode = 'NL'
     * 
     * @return array|null
     */
    public function getProducts(string $countryCode = 'NL'): ?array
    {
        return $this->request(Method::GET, "/parcel/products/$countryCode");
    }
    
    /**
     * @param string $barcode
     * 
     * @return array|null
     */
    public function getStatus(string $barcode): ?array
    {
        return $this->request(Method::GET, "/parcel/status/$barcode");
    }
}
