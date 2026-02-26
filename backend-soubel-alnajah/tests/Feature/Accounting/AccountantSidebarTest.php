<?php

namespace Tests\Feature\Accounting;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect;
use Mcamara\LaravelLocalization\Middleware\LocalizationRedirect;
use Mcamara\LaravelLocalization\Middleware\LocaleViewPath;
use Tests\TestCase;

class AccountantSidebarTest extends TestCase
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

    public function test_accountant_sidebar_shows_only_accounting_navigation(): void
    {
        $user = User::factory()->create([
            'must_change_password' => false,
        ]);

        Role::firstOrCreate(['name' => 'accountant']);
        $user->attachRole('accountant');

        $response = $this->actingAs($user)->get(route('accounting.contracts.index'));
        $response->assertStatus(200);

        $response->assertSee('العقود المالية');
        $response->assertSee('الدفعات والوصولات');

        $response->assertDontSee('/Students', false);
        $response->assertDontSee('/Teachers', false);
        $response->assertDontSee('/Inscriptions', false);
    }
}
