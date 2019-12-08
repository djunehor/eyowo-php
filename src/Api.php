<?php

namespace Djunehor\Eyowo;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Psr\Http\Message\MessageInterface;

class Api
{

    private $baseUrl = "https://api.console.eyowo.com/";
    private $appKey;
    private $appSecret;
    public $client;
    protected $output;
    protected $factors = ['sms'];
    protected $providers = ['etisalat', 'glo', 'airtel', 'mtn'];
    protected $errorCodes = [
        400 => 'Bad Request -- Your request is invalid.',
        401 => 'Unauthorized -- Your API key is wrong.',
        403 => 'Forbidden -- The kitten requested is hidden for administrators only.',
        404 => 'Not Found -- The specified kitten could not be found.',
        405 => 'Method Not Allowed -- You tried to access a kitten with an invalid method.',
        406 => "Not Acceptable -- You requested a format that isn't json.",
        410 => 'Gone -- The kitten requested has been removed from our servers.',
        418 => "I'm a teapot.",
        429 => "Too Many Requests -- You're requesting too many kittens! Slow down!",
        500 => "Internal Server Error -- We had a problem with our server. Try again later.",
        503 => "Service Unavailable -- We're temporarily offline for maintenance. Please try again later."
    ];

    protected $errorMessage;
    protected $statusCode;

    public function __construct($appKey = null)
    {
        $this->appKey = isset($appKey) ? $appKey : getenv('EYOWO_APP_KEY');

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'headers' => [
                'X-App-Key' => $this->appKey,
                'Content-Type' => 'application/json',
            ]
        ]);;
    }

    public function resolveResponse(MessageInterface $response)
    {
        $this->statusCode = $response->getStatusCode();
        $this->errorMessage = array_key_exists($this->statusCode, $this->errorCodes) ? $this->errorCodes[$this->statusCode] : '';

        return $this->output = json_decode($response->getBody()->getContents(), true);
    }

    public function handleException(ClientException $exception)
    {
        $this->statusCode = $exception->getCode();
        $this->errorMessage = $exception->getMessage();

        $output = json_decode($exception->getResponse()->getBody(), true);
        $this->output['success'] = false;
        $this->output['message'] = array_key_exists('message', $output) ? $output['message'] : $this->errorMessage;

        return $this->output;

    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getError()
    {
        return $this->errorMessage;
    }

    public function validate($phone)
    {
        try {
            $response = $this->client->post('/v1/users/auth/validate', [
                'form_params' => [
                    'mobile' => $phone
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);

    }

    public function initiateAuthorization($phone, $factor = 'sms')
    {
        if (!in_array($factor, $this->factors)) {
            throw new \Exception("Factor [$factor] not available. Available factors are: " . join(", ", $this->factors));
        }

        try {
            $response = $this->client->post('/v1/users/auth', [
                'form_params' => [
                    'mobile' => $phone,
                    'factor' => $factor
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function generateToken($phone, $passcode, $factor = 'sms')
    {
        if (!in_array($factor, $this->factors)) {
            throw new \Exception("Factor [$factor] not available. Available factors are: " . join(", ", $this->factors));
        }

        try {
            $response = $this->client->post('/v1/users/auth', [
                'form_params' => [
                    'mobile' => $phone,
                    'factor' => $factor,
                    'passcode' => $passcode
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function refreshToken($refreshToken)
    {

        try {
            $response = $this->client->post('/v1//users/accessToken', [
                'form_params' => [
                    'refreshToken' => $refreshToken,
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function transferToPhone($walletToken, $amount, $mobile)
    {

        try {
            $response = $this->client->post('/v1/transfers/phone', [
                'headers' => ['X-App-Wallet-Access-Token' => $walletToken],
                'form_params' => [
                    'amount' => $amount,
                    'mobile' => $mobile
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function transferToBank($walletToken, $amount, $accountName, $accountNumber, $bankCode)
    {

        try {
            $response = $this->client->post('/v1/transfers/bank', [
                'headers' => ['X-App-Wallet-Access-Token' => $walletToken],
                'form_params' => [
                    'amount' => $amount,
                    'accountName' => $accountName,
                    'accountNumber' => $accountNumber,
                    'bankCode' => $bankCode
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function vtu($walletToken, $mobile, $amount, $provider)
    {

        if (!in_array(strtolower($provider), $this->providers)) {
            throw new \Exception("Provider [$provider] not supported. Supported providers are: " . join(",", $this->providers));
        }

        try {
            $response = $this->client->post('/v1/users/payments/bills/vtu', [
                'headers' => ['X-App-Wallet-Access-Token' => $walletToken],
                'form_params' => [
                    'amount' => $amount,
                    'mobile' => $mobile,
                    'provider' => $provider
                ]
            ]);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function balance($walletToken)
    {

        try {
            $response = $this->client->get('/v1/users/accessToken', [
                'headers' => ['X-App-Wallet-Access-Token' => $walletToken]
            ]);

            return $this->resolveResponse($response);
        } catch (ClientException $e) {
            return $this->handleException($e);
        }
    }

    public function banks()
    {
        try {
            $response = $this->client->get('/v1/queries/banks');
        } catch (ClientException $e) {
            return $this->handleException($e);
        }

        return $this->resolveResponse($response);
    }

    public function getBanks()
    {
        return isset($this->output['data']['banks']) ? $this->output['data']['banks'] : [];
    }

    public function getWalletBalance()
    {
        return isset($this->output['balance']) ? $this->output['balance'] : 0;
    }

    public function getTokenData()
    {
        return isset($this->output['data']) ? $this->output['data'] : [];
    }

    public function getAccessToken()
    {
        $tokenData = $this->getTokenData();
        return isset($tokenData['accessToken']) ? $tokenData['accessToken'] : null;
    }

    public function getRefreshToken()
    {
        $tokenData = $this->getTokenData();
        return isset($tokenData['refreshToken']) ? $tokenData['refreshToken'] : null;
    }

    public function getUser()
    {
        return isset($this->output['data'], $this->output['data']['user']) ? $this->output['data']['user'] : [];
    }

    public function mobileIsValid()
    {
        return isset($this->output['success']);
    }

}
