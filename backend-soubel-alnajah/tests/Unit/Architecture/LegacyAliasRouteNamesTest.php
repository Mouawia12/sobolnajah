<?php

namespace Tests\Unit\Architecture;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LegacyAliasRouteNamesTest extends TestCase
{
    public function test_legacy_alias_route_names_are_registered(): void
    {
        $this->assertTrue(Route::has('legacy.lookup.schoolGrades'));
        $this->assertTrue(Route::has('legacy.lookup.gradeClasses'));
        $this->assertTrue(Route::has('legacy.lookup.classSections'));
        $this->assertTrue(Route::has('legacy.lookup.sectionById'));
        $this->assertTrue(Route::has('legacy.public.agenda.show'));
        $this->assertTrue(Route::has('legacy.public.agenda.grades'));
        $this->assertTrue(Route::has('legacy.public.gallery.index'));
        $this->assertTrue(Route::has('changepass'));
        $this->assertTrue(Route::has('DisplqyNoteFromAdmin'));
    }

    public function test_legacy_alias_routes_keep_their_old_uris(): void
    {
        $this->assertStringContainsString('/getgrade/1', route('legacy.lookup.schoolGrades', ['id' => 1], false));
        $this->assertStringContainsString('/getclasse/1', route('legacy.lookup.gradeClasses', ['id' => 1], false));
        $this->assertStringContainsString('/getsection/1', route('legacy.lookup.classSections', ['id' => 1], false));
        $this->assertStringContainsString('/getsection2/1', route('legacy.lookup.sectionById', ['id' => 1], false));
        $this->assertStringContainsString('/agenda/1', route('legacy.public.agenda.show', ['id' => 1], false));
        $this->assertStringContainsString('/album', route('legacy.public.gallery.index', [], false));
        $this->assertStringContainsString('/changepass', route('changepass', [], false));
    }
}
