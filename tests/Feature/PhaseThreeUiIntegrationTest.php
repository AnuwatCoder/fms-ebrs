<?php

use App\Enums\BorrowRequestStatus;
use App\Models\BorrowRequest;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('shows the OIDC login page to guests using only local template assets', function () {
    $response = $this->get(route('login'));

    $response
        ->assertOk()
        ->assertSee('OpenID Connect')
        ->assertSee(asset('fonts/fonts.css'), false)
        ->assertSee(asset('template/vendor/bootstrap/css/bootstrap.min.css'), false)
        ->assertSee(asset('template/assets/css/auth.css'), false)
        ->assertSee(asset('template/vendor/lucide/lucide.min.js'), false)
        ->assertSee('aria-label="FMS EBRS หน้าเข้าสู่ระบบ"', false)
        ->assertSee('class="login-wordmark"', false)
        ->assertDontSee('class="login-brand-mark"', false)
        ->assertSee('class="login-workflow-card"', false)
        ->assertDontSee('cdn.jsdelivr.net', false)
        ->assertDontSee('type="password"', false);
});

it('routes guests to login and authenticated users to the dashboard', function () {
    $this->get(route('home'))->assertRedirect(route('login'));
    $this->get(route('dashboard'))->assertRedirect(route('login'));

    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('home'))
        ->assertRedirect(route('dashboard'));
});

it('renders a permission-aware dashboard and limits borrowers to their own requests', function () {
    $this->seed(RolePermissionSeeder::class);

    $borrower = User::factory()->create(['name' => 'Borrower One']);
    $borrower->assignRole('Borrower');
    $anotherUser = User::factory()->create();

    BorrowRequest::query()->create([
        'request_no' => 'BR-OWN-001',
        'user_id' => $borrower->id,
        'purpose' => 'UI integration test',
        'borrow_date' => today(),
        'expected_return_date' => today()->addDay(),
        'status' => BorrowRequestStatus::Pending,
    ]);

    BorrowRequest::query()->create([
        'request_no' => 'BR-OTHER-001',
        'user_id' => $anotherUser->id,
        'purpose' => 'Must not be visible',
        'borrow_date' => today(),
        'expected_return_date' => today()->addDay(),
        'status' => BorrowRequestStatus::Pending,
    ]);

    $response = $this->actingAs($borrower)
        ->get(route('dashboard'));

    $response
        ->assertOk()
        ->assertSee('Borrower One')
        ->assertSee('BR-OWN-001')
        ->assertDontSee('BR-OTHER-001')
        ->assertSee(asset('template/assets/css/theme.css'), false)
        ->assertSee(asset('template/vendor/apexcharts/apexcharts.min.js'), false)
        ->assertSee('data-dashboard-role="borrower"', false)
        ->assertSee('ติดตามการยืมของคุณได้ในที่เดียว')
        ->assertSee('data-dashboard-chart="request-trend"', false)
        ->assertSee('data-dashboard-chart="distribution"', false)
        ->assertSee('data-dashboard-table="recent-requests"', false)
        ->assertDontSee('cdn.jsdelivr.net', false);

    $response
        ->assertViewHas('requestStats', fn (array $stats): bool => $stats['total'] === 1 && $stats['pending'] === 1)
        ->assertViewHas('canViewEquipment', false)
        ->assertViewHas('requestTrend', fn (array $trend): bool => array_sum($trend['submitted']) === 1)
        ->assertViewHas(
            'distributionChart',
            fn (array $chart): bool => $chart['title'] === 'สถานะคำขอของฉัน' && array_sum($chart['series']) === 1,
        );
});

it('renders a distinct dashboard for every core role', function () {
    $this->seed(RolePermissionSeeder::class);

    $expectations = [
        'Borrower' => [
            'key' => 'borrower',
            'title' => 'ติดตามการยืมของคุณได้ในที่เดียว',
            'action' => 'borrow.create',
            'hidden_action' => 'approval.index',
        ],
        'Approver' => [
            'key' => 'approver',
            'title' => 'พิจารณาคำขอได้อย่างรวดเร็วและชัดเจน',
            'action' => 'approval.index',
            'hidden_action' => 'checkout.index',
        ],
        'Staff' => [
            'key' => 'staff',
            'title' => 'บริหารคิวจ่ายและรับคืนอุปกรณ์',
            'action' => 'checkout.index',
            'hidden_action' => 'admin.users',
        ],
        'Admin' => [
            'key' => 'admin',
            'title' => 'ภาพรวมการดำเนินงานและทรัพยากร',
            'action' => 'return.index',
            'hidden_action' => 'admin.users',
        ],
        'SuperAdmin' => [
            'key' => 'superadmin',
            'title' => 'ศูนย์ควบคุมระบบ FMS EBRS',
            'action' => 'admin.users',
            'hidden_action' => 'borrow.create',
        ],
    ];

    foreach ($expectations as $role => $expectation) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('data-dashboard-role="'.$expectation['key'].'"', false)
            ->assertSee('class="dashboard-hero dashboard-hero-'.$expectation['key'].'"', false)
            ->assertSee($expectation['title'])
            ->assertSee('data-dashboard-action="'.$expectation['action'].'"', false)
            ->assertDontSee('data-dashboard-action="'.$expectation['hidden_action'].'"', false);
    }
});

it('renders navigation for every application role without exposing unauthorized menus', function () {
    $this->seed(RolePermissionSeeder::class);

    $expectations = [
        'Borrower' => [
            'visible' => ['dashboard', 'equipment.index', 'borrow.create', 'borrow.mine'],
            'hidden' => ['borrow.index', 'approval.index', 'checkout.index', 'report.equipment', 'admin.users'],
        ],
        'Approver' => [
            'visible' => ['dashboard', 'borrow.index', 'approval.index'],
            'hidden' => ['equipment.index', 'borrow.mine', 'checkout.index', 'report.equipment', 'admin.users'],
        ],
        'Staff' => [
            'visible' => ['dashboard', 'equipment.index', 'maintenance.index', 'borrow.index', 'checkout.index', 'return.index'],
            'hidden' => ['borrow.mine', 'approval.index', 'report.equipment', 'admin.users'],
        ],
        'Admin' => [
            'visible' => [
                'dashboard',
                'equipment.index',
                'category.index',
                'maintenance.index',
                'borrow.index',
                'approval.index',
                'checkout.index',
                'return.index',
                'report.equipment',
                'report.borrowing',
                'report.overdue',
                'report.damage',
                'audit.index',
            ],
            'hidden' => ['borrow.mine', 'admin.users', 'admin.roles', 'admin.permissions', 'admin.settings'],
        ],
        'SuperAdmin' => [
            'visible' => [
                'dashboard',
                'equipment.index',
                'category.index',
                'maintenance.index',
                'borrow.create',
                'borrow.mine',
                'borrow.index',
                'approval.index',
                'checkout.index',
                'return.index',
                'report.equipment',
                'report.borrowing',
                'report.overdue',
                'report.damage',
                'admin.users',
                'admin.roles',
                'admin.permissions',
                'admin.settings',
                'audit.index',
            ],
            'hidden' => [],
        ],
    ];

    foreach ($expectations as $role => $menus) {
        $user = User::factory()->create();
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();

        foreach ($menus['visible'] as $menu) {
            $response->assertSee('data-menu-key="'.$menu.'"', false);
        }

        foreach ($menus['hidden'] as $menu) {
            $response->assertDontSee('data-menu-key="'.$menu.'"', false);
        }
    }
});

it('renders the completed category and maintenance menus as working links', function () {
    $this->seed(RolePermissionSeeder::class);

    $user = User::factory()->create();
    $user->assignRole('SuperAdmin');

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="'.route('category.index').'"', false)
        ->assertSee('href="'.route('maintenance.index').'"', false)
        ->assertDontSee('class="menu-status"', false);
});

it('ships every frontend dependency referenced by the layouts', function () {
    $assets = [
        'fonts/fonts.css',
        'fonts/Anuphan-Regular.woff',
        'fonts/Anuphan-SemiBold.woff',
        'fonts/Anuphan-Bold.woff',
        'template/vendor/bootstrap/css/bootstrap.min.css',
        'template/vendor/bootstrap/js/bootstrap.bundle.min.js',
        'template/vendor/lucide/lucide.min.js',
        'template/vendor/apexcharts/apexcharts.min.js',
        'template/assets/css/auth.css',
        'template/assets/css/theme.css',
        'template/assets/css/ebrs.css',
        'template/assets/js/app.js',
    ];

    foreach ($assets as $asset) {
        expect(public_path($asset))->toBeFile();
    }
});
