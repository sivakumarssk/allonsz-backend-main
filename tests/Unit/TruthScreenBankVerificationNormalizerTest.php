<?php

namespace Tests\Unit;

use App\Http\Controllers\TruthScreenController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class TruthScreenBankVerificationNormalizerTest extends TestCase
{
    /**
     * @dataProvider providerMappings
     */
    public function test_it_maps_provider_payload_to_stable_error_code(array $payload, string $expectedCode): void
    {
        $controller = new TruthScreenController();
        $method = (new ReflectionClass($controller))->getMethod('normalizeBankVerificationFailure');
        $method->setAccessible(true);

        $result = $method->invoke($controller, $payload);

        $this->assertSame($expectedCode, $result['code']);
        $this->assertIsString($result['message']);
        $this->assertNotEmpty($result['message']);
    }

    public function providerMappings(): array
    {
        return [
            'invalid ifsc' => [['msg' => ['status' => 'Invalid IFSC']], 'INVALID_IFSC'],
            'invalid account' => [['msg' => ['status' => 'Invalid account number']], 'INVALID_ACCOUNT_NUMBER'],
            'account ifsc mismatch' => [['msg' => ['status' => 'Account IFSC mismatch']], 'ACCOUNT_IFSC_MISMATCH'],
            'account inactive' => [['msg' => ['status' => 'Account not active']], 'ACCOUNT_NOT_ACTIVE'],
            'unknown fallback' => [['foo' => 'bar'], 'BANK_VERIFICATION_FAILED'],
        ];
    }
}

