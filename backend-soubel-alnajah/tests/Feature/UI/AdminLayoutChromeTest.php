<?php

namespace Tests\Feature\UI;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AdminLayoutChromeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            LocaleSessionRedirect::class,
            LocalizationRedirect::class,
            LocaleViewPath::class,
        ]);
    }

    public function test_admin_layout_renders_breadcrumbs_on_accounting_page(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)->get(route('accounting.contracts.index'));

        $response->assertStatus(200);
        $response->assertSee('لوحة التحكم');
        $response->assertSee('العقود المالية');
    }

    public function test_admin_layout_renders_unified_success_alert(): void
    {
        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)
            ->withSession(['success' => 'تم الحفظ بنجاح'])
            ->get(route('accounting.contracts.index'));

        $response->assertStatus(200);
        $response->assertSee('تم الحفظ بنجاح');
    }

    public function test_admin_layout_sets_rtl_html_attributes_for_arabic_locale(): void
    {
        app()->setLocale('ar');

        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)->get(route('accounting.contracts.index'));

        $response->assertStatus(200);
        $response->assertSee('lang="ar"', false);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_admin_layout_sets_ltr_html_attributes_for_non_arabic_locale(): void
    {
        app()->setLocale('en');

        $user = User::factory()->create(['must_change_password' => false]);
        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)->get(route('accounting.contracts.index'));

        $response->assertStatus(200);
        $response->assertSee('lang="en"', false);
        $response->assertSee('dir="ltr"', false);
    }
}
