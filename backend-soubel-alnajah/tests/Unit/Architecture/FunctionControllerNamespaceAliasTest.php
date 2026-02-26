<?php

namespace Tests\Unit\Architecture;

use PHPUnit\Framework\TestCase;

class FunctionControllerNamespaceAliasTest extends TestCase
{
    public function test_legacy_functionn_controller_extends_canonical_function_controller(): void
    {
        $legacy = \App\Http\Controllers\Functionn\FunctionController::class;
        $canonical = \App\Http\Controllers\Function\FunctionController::class;

        $this->assertTrue(class_exists($legacy));
        $this->assertTrue(class_exists($canonical));
        $this->assertTrue(is_subclass_of($legacy, $canonical));
    }
}

