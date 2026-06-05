<?php

namespace Tests\Feature\Inscription;

use App\Models\School\Classroom;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Section;
use App\Services\SchoolStructureSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolStructureSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    private SchoolStructureSyncService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SchoolStructureSyncService();
    }

    public function test_it_creates_full_hierarchy_for_new_school(): void
    {
        $school = $this->service->resolveSchool('ثانوية سبل النجاح (الوادي)');
        $section = $this->service->resolveSection($school, 'أولى', 'جدع مشترك آداب', '1');

        $this->assertSame('ثانوية سبل النجاح (الوادي)', $school->getTranslation('name_school', 'ar'));
        $this->assertSame((int) $school->id, (int) $section->school_id);
        $this->assertSame('1', $section->getTranslation('name_section', 'ar'));
        $this->assertSame(1, (int) $section->Status);

        $classroom = Classroom::find($section->classroom_id);
        $this->assertSame('جدع مشترك آداب', $classroom->getTranslation('name_class', 'ar'));

        $grade = Schoolgrade::find($section->grade_id);
        $this->assertSame('أولى', $grade->getTranslation('name_grade', 'ar'));

        $this->assertSame(
            ['schools' => 1, 'grades' => 1, 'classrooms' => 1, 'sections' => 1],
            $this->service->created
        );
    }

    public function test_it_reuses_existing_school_by_arabic_name(): void
    {
        $existing = new School();
        $existing->setTranslations('name_school', ['ar' => 'مدرسة موجودة', 'fr' => 'مدرسة موجودة', 'en' => 'مدرسة موجودة']);
        $existing->save();

        $resolved = $this->service->resolveSchool('مدرسة موجودة');

        $this->assertSame((int) $existing->id, (int) $resolved->id);
        $this->assertSame(0, $this->service->created['schools']);
        $this->assertSame(1, School::count());
    }

    public function test_it_uses_default_classroom_when_stream_is_missing(): void
    {
        $school = $this->service->resolveSchool('متوسطة التجربة');
        $section = $this->service->resolveSection($school, 'أولى', null, '1');

        $classroom = Classroom::find($section->classroom_id);

        $this->assertSame(SchoolStructureSyncService::DEFAULT_CLASSROOM_NAME, $classroom->getTranslation('name_class', 'ar'));
    }

    public function test_it_does_not_duplicate_structure_on_repeated_calls(): void
    {
        $school = $this->service->resolveSchool('ثانوية التجربة');

        $first = $this->service->resolveSection($school, 'أولى', 'علوم تجريبية', '2');
        $second = $this->service->resolveSection($school, 'أولى', 'علوم تجريبية', '2');

        $this->assertSame((int) $first->id, (int) $second->id);
        $this->assertSame(1, Schoolgrade::count());
        $this->assertSame(1, Classroom::count());
        $this->assertSame(1, Section::count());
        $this->assertSame(1, $this->service->created['sections']);
    }

    public function test_it_caches_lookups_in_memory(): void
    {
        $school = $this->service->resolveSchool('ثانوية التجربة');
        $this->service->resolveSection($school, 'أولى', 'آداب', '1');

        DB::enableQueryLog();
        $this->service->resolveSection($school, 'أولى', 'آداب', '1');
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // كل المستويات في الكاش → لا أي استعلام إضافي
        $this->assertCount(0, $queries);
    }

    public function test_same_section_name_in_different_streams_creates_separate_sections(): void
    {
        $school = $this->service->resolveSchool('ثانوية التجربة');

        $sectionArts = $this->service->resolveSection($school, 'أولى', 'جدع مشترك آداب', '1');
        $sectionScience = $this->service->resolveSection($school, 'أولى', 'جدع مشترك علوم وتكنولوجيا', '1');

        $this->assertNotSame((int) $sectionArts->id, (int) $sectionScience->id);
        $this->assertSame(1, Schoolgrade::count());
        $this->assertSame(2, Classroom::count());
        $this->assertSame(2, Section::count());
    }

    public function test_same_grade_name_in_different_schools_is_separate(): void
    {
        $highSchool = $this->service->resolveSchool('ثانوية أ');
        $middleSchool = $this->service->resolveSchool('متوسطة ب');

        $this->service->resolveSection($highSchool, 'أولى', 'آداب', '1');
        $this->service->resolveSection($middleSchool, 'أولى', null, '1');

        $this->assertSame(2, Schoolgrade::count());
        $this->assertSame(
            1,
            Schoolgrade::where('school_id', $highSchool->id)->count()
        );
    }

    public function test_it_normalizes_names_with_extra_whitespace(): void
    {
        $school = $this->service->resolveSchool('  ثانوية   التجربة  ');

        $this->assertSame('ثانوية التجربة', $school->getTranslation('name_school', 'ar'));

        // نفس الاسم بمسافات مختلفة → نفس المدرسة
        $again = $this->service->resolveSchool('ثانوية التجربة');
        $this->assertSame((int) $school->id, (int) $again->id);
        $this->assertSame(1, School::count());
    }
}
