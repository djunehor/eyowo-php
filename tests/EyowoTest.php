<?php

namespace Djunehor\Eyowo\Tests;

use Djunehor\Eyowo\Api;
use Dotenv\Dotenv;
use GuzzleHttp\Client;

class EyowoTest extends \PHPUnit_Framework_TestCase
{
    private $phone = '2348132349845';

    public static function setUpBeforeClass()
    {
        $env = new DotEnv(__DIR__ . '/..');
        $env->load();
    }

    public function testInitializeClientOnConstruct()
    {
        $eyowo = new Api();
        $this->assertInstanceOf(Client::class, $eyowo->client);
    }

    public function testValidateUser()
    {
        $eyowo = new Api();
        $response = $eyowo->validate($this->phone);
        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testInitializeAuthorizationInValidFactor()
    {
        $this->expectException(\Exception::class);
        $eyowo = new Api();
        $eyowo->initiateAuthorization($this->phone, 'mms');
    }

    public function testInitializeAuthorization()
    {
        $eyowo = new Api();
        $response = $eyowo->initiateAuthorization($this->phone, 'sms');

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testGenerateToken()
    {
        $eyowo = new Api();
        $response = $eyowo->generateToken($this->phone, rand(111111, 888888), 'sms');

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);

        $this->assertNull($eyowo->getAccessToken());
        $this->assertNull($eyowo->getRefreshToken());
    }

    public function testRefreshToken()
    {
        $eyowo = new Api();
        $response = $eyowo->refreshToken(rand(111111111, 999999999));

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);

        $this->assertNotEmpty($eyowo->getError());
    }

    public function testTransferToPhone()
    {
        $eyowo = new Api();
        $response = $eyowo->transferToPhone(rand(111111111, 999999999), rand(10000, 100000), $this->phone);

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testTransferToBank()
    {
        $eyowo = new Api();
        $response = $eyowo->transferToBank(
            rand(111111111, 999999999),
            rand(10000, 100000),
            'John Doe',
            rand(01111111111, 19999999999),
            42
        );

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testVtuInvalidProvider()
    {
        $this->expectException(\Exception::class);
        $eyowo = new Api();
        $eyowo->vtu(
            rand(111111111, 999999999),
            $this->phone,
            rand(10000, 100000),
            'telnet'
        );
    }

    public function testVtu()
    {
        $eyowo = new Api();
        $response = $eyowo->vtu(
            rand(111111111, 999999999),
            $this->phone,
            rand(10000, 100000),
            'mtn'
        );

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testBalance()
    {
        $eyowo = new Api();
        $response = $eyowo->balance(rand(111111111, 999999999));

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('message', $response);
    }

    public function testGetBalance()
    {
        $eyowo = new Api();
        $eyowo->balance(rand(111111111, 999999999));
        $balance = $eyowo->getWalletBalance();

        $this->assertEquals(0, $balance);
    }

    public function testBanks()
    {
        $eyowo = new Api();
        $response = $eyowo->banks();

        $bank = [
            "bankCode" => "090270",
            "bankName" => "AB MICROFINANCE BANK"
        ];

        $this->assertArrayHasKey('success', $response);
        $this->assertArrayHasKey('data', $response);
        $this->assertArrayHasKey('banks', $response['data']);
        $this->assertEquals($bank, $response['data']['banks'][0]);
    }

    public function testGetBanks()
    {
        $eyowo = new Api();
        $eyowo->banks();

        $bank = [
            "bankCode" => "090270",
            "bankName" => "AB MICROFINANCE BANK"
        ];

        $banks = $eyowo->getBanks();

        $this->assertNotEmpty($banks);
        $this->assertEquals($bank, $banks[0]);
    }
}
