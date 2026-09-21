<?php

namespace App\Support;

/**
 * كتالوج أقسام السايدبار: المصدر الوحيد لبناء القائمة وجدول الصلاحيات.
 * كل قسم = مفتاح ثابت + تسمية + روابط (اسم مسار + تسمية + معاملات اختيارية).
 */
class MenuCatalog
{
    /**
     * أقسام قابلة للتحكم بها في جدول الصلاحيات (تظهر/تختفي حسب الدور).
     *
     * @return array<string, array{label:string, icon:string, links:array<int, array{route:string, label:string, params?:array}>}>
     */
    public static function sections(): array
    {
        return [
            'teacher_portal' => [
                'label' => __('لوحة المعلم'),
                'icon' => 'mdi mdi-view-dashboard',
                'links' => [
                    ['route' => 'teacher.dashboard', 'label' => __('الرئيسية')],
                    ['route' => 'teacher.schedules.index', 'label' => trans('main_sidebar.teacher_weekly_schedule')],
                ],
            ],
            'accountant_portal' => [
                'label' => __('لوحة المحاسب'),
                'icon' => 'mdi mdi-view-dashboard',
                'links' => [
                    ['route' => 'accountant.dashboard', 'label' => __('لوحة المحاسب')],
                ],
            ],
            'user_management' => [
                'label' => __('إدارة المستخدمين'),
                'icon' => 'mdi mdi-account-multiple-plus',
                'links' => [
                    ['route' => 'accounts.index', 'label' => trans('accounts.title')],
                    ['route' => 'admin.users.create', 'label' => __('إضافة مستخدم')],
                ],
            ],
            'school_settings' => [
                'label' => __('إعدادات المدرسة'),
                'icon' => 'mdi mdi-school',
                'links' => [
                    ['route' => 'Schools.index', 'label' => trans('main_sidebar.addecoles')],
                    ['route' => 'Schoolgrades.index', 'label' => trans('main_sidebar.addclasse')],
                    ['route' => 'Classes.index', 'label' => trans('main_sidebar.addclasseroom')],
                    ['route' => 'Sections.index', 'label' => trans('main_sidebar.addsection')],
                ],
            ],
            'students' => [
                'label' => __('الطلاب'),
                'icon' => 'si-people si',
                'links' => [
                    ['route' => 'Inscriptions.index', 'label' => trans('inscription.studentinscription')],
                    ['route' => 'Students.index', 'label' => trans('main_sidebar.studentlist')],
                    ['route' => 'Students.create', 'label' => trans('main_sidebar.addstudent')],
                    ['route' => 'Parents.index', 'label' => __('أولياء الأمور')],
                    ['route' => 'Promotions.index', 'label' => trans('main_sidebar.promotion')],
                    ['route' => 'graduated.index', 'label' => trans('main_sidebar.graduated')],
                    ['route' => 'Absences.index', 'label' => trans('main_sidebar.Absences')],
                    ['route' => 'attendance.record', 'label' => trans('main_sidebar.attendance_record')],
                ],
            ],
            'teachers' => [
                'label' => __('المعلمون'),
                'icon' => 'si-people si',
                'links' => [
                    ['route' => 'Teachers.index', 'label' => trans('teacher.teacherlist')],
                ],
            ],
            'assessments' => [
                'label' => trans('academic.assessments'),
                'icon' => 'mdi mdi-clipboard-text',
                'links' => [
                    ['route' => 'assessments.index', 'label' => trans('academic.assessments')],
                ],
            ],
            'hr' => [
                'label' => trans('hr.employees'),
                'icon' => 'si-people si',
                'links' => [
                    ['route' => 'employees.index', 'label' => trans('hr.employees')],
                    ['route' => 'staff-attendance.record', 'label' => trans('hr.teachers_attendance'), 'params' => ['teachers']],
                    ['route' => 'staff-attendance.record', 'label' => trans('hr.employees_attendance'), 'params' => ['employees']],
                    ['route' => 'staff-attendance.report', 'label' => trans('hr.attendance_report') . ' — ' . trans('hr.teachers_attendance'), 'params' => ['teachers']],
                    ['route' => 'staff-attendance.report', 'label' => trans('hr.attendance_report') . ' — ' . trans('hr.employees_attendance'), 'params' => ['employees']],
                ],
            ],
            'content' => [
                'label' => __('الأجندة والمحتوى'),
                'icon' => 'icon-Write',
                'links' => [
                    ['route' => 'Agendas.index', 'label' => trans('main_sidebar.agenda')],
                    ['route' => 'Grades.index', 'label' => trans('main_sidebar.grades')],
                    ['route' => 'timetables.index', 'label' => trans('main_sidebar.timetables')],
                    ['route' => 'teacher-schedules.index', 'label' => trans('main_sidebar.teacher_schedules')],
                    ['route' => 'Publications.index', 'label' => trans('main_sidebar.publication')],
                    ['route' => 'Exames.index', 'label' => trans('exam.exam')],
                ],
            ],
            'recruitment' => [
                'label' => trans('main_header.recruitment'),
                'icon' => 'fa fa-briefcase',
                'links' => [
                    ['route' => 'JobPosts.index', 'label' => trans('main_sidebar.recruitment_posts')],
                    ['route' => 'recruitment.applications.index', 'label' => trans('main_sidebar.recruitment_applications')],
                ],
            ],
            'finance' => [
                'label' => trans('main_sidebar.finance'),
                'icon' => 'mdi mdi-cash-multiple',
                'links' => [
                    ['route' => 'accounting.contracts.index', 'label' => trans('main_sidebar.finance_contracts')],
                    ['route' => 'accounting.payments.index', 'label' => trans('main_sidebar.finance_payments')],
                ],
            ],
            'communication' => [
                'label' => trans('opt.application'),
                'icon' => 'si-layers si',
                'links' => [
                    ['route' => 'chat.ai', 'label' => trans('opt.chatai')],
                    ['route' => 'Chats.index', 'label' => trans('opt.chat_users')],
                ],
            ],
        ];
    }

    /**
     * مفاتيح الأقسام القابلة للتحكم فقط.
     *
     * @return array<int, string>
     */
    public static function keys(): array
    {
        return array_keys(static::sections());
    }

    /** علامة تُخزَّن عند ضبط دور (ولو بلا أقسام) لتمييز «مضبوط فارغ» عن «غير مضبوط». */
    public const CONFIGURED_MARKER = '__configured__';

    /**
     * الأقسام الافتراضية لدور معروف عندما لا يُضبط صراحةً بعد.
     *
     * @return array<int, string>
     */
    public static function defaultsForRole(string $roleName): array
    {
        return [
            'accountant' => ['finance', 'accountant_portal', 'communication'],
            'teacher' => ['teacher_portal', 'assessments', 'communication'],
            'supervisor' => ['hr'],
        ][$roleName] ?? [];
    }
}
