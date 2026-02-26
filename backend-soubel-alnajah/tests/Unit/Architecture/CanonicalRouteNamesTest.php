<?php

namespace Tests\Unit\Architecture;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CanonicalRouteNamesTest extends TestCase
{
    public function test_canonical_route_names_are_registered(): void
    {
        $this->assertTrue(Route::has('lookup.schoolGrades'));
        $this->assertTrue(Route::has('lookup.gradeClasses'));
        $this->assertTrue(Route::has('lookup.classSections'));
        $this->assertTrue(Route::has('lookup.sectionById'));
        $this->assertTrue(Route::has('public.agenda.show'));
        $this->assertTrue(Route::has('public.agenda.grades'));
        $this->assertTrue(Route::has('public.gallery.index'));
        $this->assertTrue(Route::has('admin.password.change.page'));
    }
}

