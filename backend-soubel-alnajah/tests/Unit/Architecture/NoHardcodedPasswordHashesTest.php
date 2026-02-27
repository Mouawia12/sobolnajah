<?php

namespace Tests\Unit\Architecture;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class NoHardcodedPasswordHashesTest extends TestCase
{
    public function test_app_code_does_not_hash_string_literals_as_passwords(): void
    {
        $appPath = app_path();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appPath));
        $violations = [];

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($file->getPathname()) ?: '';

            if (preg_match('/Hash::make\(\s*[\'"]/', $contents) === 1) {
                $violations[] = str_replace(base_path() . '/', '', $file->getPathname()) . ': Hash::make(literal)';
            }

            if (preg_match('/bcrypt\(\s*[\'"]/', $contents) === 1) {
                $violations[] = str_replace(base_path() . '/', '', $file->getPathname()) . ': bcrypt(literal)';
            }
        }

        $this->assertSame([], $violations, 'Hardcoded password hash patterns found: ' . implode('; ', $violations));
    }
}
