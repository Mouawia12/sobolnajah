<?php

namespace Tests\Unit\Inscription;

use App\Actions\Inscription\BuildLocalizedNameAction;
use PHPUnit\Framework\TestCase;

class BuildLocalizedNameActionTest extends TestCase
{
    public function test_it_builds_localized_name_with_en_fallback(): void
    {
        $action = new BuildLocalizedNameAction();

        $result = $action->execute('Ali', 'علي');

        $this->assertSame([
            'fr' => 'Ali',
            'ar' => 'علي',
            'en' => 'Ali',
        ], $result);
    }

    public function test_it_can_remove_null_values_when_requested(): void
    {
        $action = new BuildLocalizedNameAction();

        $result = $action->execute(null, 'علي', null, true);

        $this->assertSame([
            'ar' => 'علي',
            'en' => 'علي',
        ], $result);
    }
}
