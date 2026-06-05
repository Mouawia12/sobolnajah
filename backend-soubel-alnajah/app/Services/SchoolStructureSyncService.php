<?php

namespace App\Services;

use App\Models\School\Classroom;
use App\Models\School\School;
use App\Models\School\Schoolgrade;
use App\Models\School\Section;

/**
 * مزامنة الهيكل المدرسي (مدرسة → سنة → شعبة/قسم تربوي → فوج) أثناء الاستيراد:
 * بحث-أو-إنشاء مع كاش في الذاكرة لتفادي استعلام لكل صف.
 */
class SchoolStructureSyncService
{
    /** اسم الشعبة الافتراضي عندما لا يحتوي الملف عمود "الشعبة" (المتوسط/الابتدائي). */
    public const DEFAULT_CLASSROOM_NAME = 'عام';

    /** @var array<string, School> */
    private array $schoolCache = [];

    /** @var array<string, Schoolgrade> */
    private array $gradeCache = [];

    /** @var array<string, Classroom> */
    private array $classroomCache = [];

    /** @var array<string, Section> */
    private array $sectionCache = [];

    /** @var array<string, int> */
    public array $created = [
        'schools' => 0,
        'grades' => 0,
        'classrooms' => 0,
        'sections' => 0,
    ];

    public function resolveSchool(string $name): School
    {
        $normalized = $this->normalizeName($name);

        if (isset($this->schoolCache[$normalized])) {
            return $this->schoolCache[$normalized];
        }

        $school = School::query()->where('name_school->ar', $normalized)->first();

        if (!$school) {
            // name_school ليست ضمن fillable في موديل School، لذلك نعيّنها مباشرة
            $school = new School();
            $school->setTranslations('name_school', ['ar' => $normalized, 'fr' => $normalized, 'en' => $normalized]);
            $school->save();
            $this->created['schools']++;
        }

        return $this->schoolCache[$normalized] = $school;
    }

    public function resolveSection(School $school, string $gradeName, ?string $streamName, string $sectionName): Section
    {
        $grade = $this->findOrCreateGrade($school, $gradeName);
        $classroomName = $this->normalizeName((string) $streamName) ?: self::DEFAULT_CLASSROOM_NAME;
        $classroom = $this->findOrCreateClassroom($grade, $classroomName);

        return $this->findOrCreateSection($classroom, $sectionName);
    }

    private function findOrCreateGrade(School $school, string $gradeName): Schoolgrade
    {
        $normalized = $this->normalizeName($gradeName);
        $cacheKey = $school->id . '|' . $normalized;

        if (isset($this->gradeCache[$cacheKey])) {
            return $this->gradeCache[$cacheKey];
        }

        $grade = Schoolgrade::query()
            ->where('school_id', $school->id)
            ->where('name_grade->ar', $normalized)
            ->first();

        if (!$grade) {
            $grade = Schoolgrade::create([
                'school_id' => $school->id,
                'name_grade' => ['ar' => $normalized, 'fr' => $normalized, 'en' => $normalized],
                'notes' => ['ar' => '', 'fr' => '', 'en' => ''],
            ]);
            $this->created['grades']++;
        }

        return $this->gradeCache[$cacheKey] = $grade;
    }

    private function findOrCreateClassroom(Schoolgrade $grade, string $className): Classroom
    {
        $normalized = $this->normalizeName($className);
        $cacheKey = $grade->id . '|' . $normalized;

        if (isset($this->classroomCache[$cacheKey])) {
            return $this->classroomCache[$cacheKey];
        }

        $classroom = Classroom::query()
            ->where('grade_id', $grade->id)
            ->where('name_class->ar', $normalized)
            ->first();

        if (!$classroom) {
            $classroom = Classroom::create([
                'school_id' => $grade->school_id,
                'grade_id' => $grade->id,
                'name_class' => ['ar' => $normalized, 'fr' => $normalized, 'en' => $normalized],
            ]);
            $this->created['classrooms']++;
        }

        return $this->classroomCache[$cacheKey] = $classroom;
    }

    private function findOrCreateSection(Classroom $classroom, string $sectionName): Section
    {
        $normalized = $this->normalizeName($sectionName);
        $cacheKey = $classroom->id . '|' . $normalized;

        if (isset($this->sectionCache[$cacheKey])) {
            return $this->sectionCache[$cacheKey];
        }

        $section = Section::query()
            ->where('classroom_id', $classroom->id)
            ->where('name_section->ar', $normalized)
            ->first();

        if (!$section) {
            $section = Section::create([
                'school_id' => $classroom->school_id,
                'grade_id' => $classroom->grade_id,
                'classroom_id' => $classroom->id,
                'name_section' => ['ar' => $normalized, 'fr' => $normalized, 'en' => $normalized],
                'Status' => 1,
            ]);
            $this->created['sections']++;
        }

        return $this->sectionCache[$cacheKey] = $section;
    }

    private function normalizeName(string $value): string
    {
        return trim((string) preg_replace('/[\x{00A0}\s]+/u', ' ', $value));
    }
}
