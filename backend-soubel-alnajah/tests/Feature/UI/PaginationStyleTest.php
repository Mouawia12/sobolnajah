<?php

namespace Tests\Feature\UI;

use App\Models\School\School;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\Paginator;
use Tests\TestCase;

class PaginationStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_pagination_uses_bootstrap_view_not_tailwind(): void
    {
        // الثيم الإداري Bootstrap — قالب Tailwind الافتراضي يعرض أسهم SVG عملاقة
        $this->assertSame('pagination::bootstrap-5', Paginator::$defaultView);
    }

    public function test_rendered_pagination_markup_is_bootstrap(): void
    {
        for ($i = 1; $i <= 25; $i++) {
            $school = new School();
            $school->setTranslations('name_school', ['ar' => "مدرسة {$i}", 'fr' => "Ecole {$i}", 'en' => "School {$i}"]);
            $school->save();
        }

        $html = (string) School::query()->paginate(10)->links();

        $this->assertStringContainsString('page-item', $html);
        $this->assertStringContainsString('pagination', $html);
        // قالب Tailwind يحتوي SVG بأسهم بدون أصناف حجم Bootstrap
        $this->assertStringNotContainsString('w-5 h-5', $html);
    }
}
