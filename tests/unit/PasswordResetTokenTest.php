<?php

declare(strict_types=1);

use App\Libraries\PasswordResetToken;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class PasswordResetTokenTest extends CIUnitTestCase
{
    public function testGenerateProduces128HexChars(): void
    {
        $token = PasswordResetToken::generate();

        $this->assertSame(128, strlen($token));
        $this->assertMatchesRegularExpression('/^[0-9a-f]{128}$/', $token);
    }

    public function testGenerateProducesUniqueValues(): void
    {
        $a = PasswordResetToken::generate();
        $b = PasswordResetToken::generate();

        $this->assertNotSame($a, $b);
    }
}
