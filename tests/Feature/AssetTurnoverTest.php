<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AssetMovement;
use App\Models\Employee;
use App\Models\PhysicalLocation;
use App\Models\PqsRecord;
use App\Models\Notification;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class AssetTurnoverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    protected function tearDown(): void
    {
        PqsRecord::flushEventListeners();

        parent::tearDown();
    }

    protected function createTables(): void
    {
        Schema::dropIfExists('asset_movements');
        Schema::dropIfExists('pqs');
        Schema::dropIfExists('physical_locations');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('accounts');

        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table): void {
                $table->bigIncrements('account_id');
                $table->string('username')->unique();
                $table->string('password');
                $table->string('role');
            });
        }

        if (! Schema::hasTable('employees')) {
            Schema::create('employees', function (Blueprint $table): void {
                $table->string('employee_id')->primary();
                $table->string('first_name');
                $table->string('middle_name')->nullable();
                $table->string('last_name');
                $table->string('suffix')->nullable();
                $table->date('date_of_birth')->nullable();
                $table->string('marital_status')->nullable();
                $table->string('gender')->nullable();
                $table->string('contact_no')->nullable();
                $table->string('email')->nullable();
                $table->unsignedBigInteger('account_id')->nullable();
                $table->string('profile_img')->nullable();
                $table->unsignedBigInteger('position_id')->nullable();
                $table->integer('section_id')->nullable();
            });
        }

        if (! Schema::hasTable('physical_locations')) {
            Schema::create('physical_locations', function (Blueprint $table): void {
                $table->increments('location_id');
                $table->string('location_name');
                $table->string('location_code')->nullable();
                $table->string('location_type');
                $table->unsignedInteger('parent_location_id')->nullable();
                $table->integer('division_id')->nullable();
                $table->integer('section_id')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('pqs')) {
            Schema::create('pqs', function (Blueprint $table): void {
                $table->string('property_no')->primary();
                $table->string('article')->nullable();
                $table->text('description')->nullable();
                $table->string('serial_number')->nullable();
                $table->date('date_acquired')->nullable();
                $table->decimal('unit_value', 15, 2)->nullable();
                $table->string('unit')->nullable();
                $table->integer('on_hand_per_count')->nullable();
                $table->decimal('total_value', 15, 2)->nullable();
                $table->string('remarks')->nullable();
                $table->string('accountable_officer_id')->nullable();
                $table->string('cat_id')->nullable();
                $table->unsignedInteger('current_location_id')->nullable();
                $table->string('current_custodian_employee_id')->nullable();
                $table->integer('assigned_division_id')->nullable();
                $table->integer('assigned_section_id')->nullable();
                $table->string('asset_status')->nullable();
                $table->timestamp('last_movement_at')->nullable();
                $table->date('last_inventory_date')->nullable();
                $table->string('last_inventoried_by')->nullable();
            });
        }

        if (! Schema::hasTable('asset_movements')) {
            Schema::create('asset_movements', function (Blueprint $table): void {
                $table->bigIncrements('movement_id');
                $table->string('property_no');
                $table->unsignedInteger('from_location_id')->nullable();
                $table->unsignedInteger('to_location_id')->nullable();
                $table->string('from_custodian_employee_id')->nullable();
                $table->string('to_custodian_employee_id')->nullable();
                $table->integer('from_division_id')->nullable();
                $table->integer('to_division_id')->nullable();
                $table->integer('from_section_id')->nullable();
                $table->integer('to_section_id')->nullable();
                $table->string('movement_type');
                $table->string('reason_code')->nullable();
                $table->timestamp('effective_at')->nullable();
                $table->unsignedBigInteger('recorded_by')->nullable();
                $table->string('source_table')->nullable();
                $table->string('source_record_id')->nullable();
                $table->text('remarks')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table): void {
                $table->bigIncrements('notification_id');
                $table->unsignedBigInteger('recipient_id');
                $table->unsignedBigInteger('sender_id')->nullable();
                $table->string('table_name')->nullable();
                $table->string('record_id')->nullable();
                $table->text('message');
                $table->string('type')->nullable();
                $table->boolean('is_read')->default(false);
                $table->timestamp('created_at')->nullable();
            });
        }
    }

    public function test_turnover_processes_all_assets_for_employee(): void
    {
        $actor = Account::query()->create([
            'username' => 'turnover.user',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $employee = Employee::query()->create([
            'employee_id' => 'EMP-9001',
            'first_name' => 'Jane',
            'last_name' => 'Dela Cruz',
            'account_id' => null,
        ]);

        $stockroom = PhysicalLocation::query()->create([
            'location_name' => 'Main Stockroom',
            'location_code' => 'STK-01',
            'location_type' => 'storage',
            'is_active' => true,
            'division_id' => 6,
            'section_id' => 11,
        ]);

        PqsRecord::query()->create([
            'property_no' => 'PQS-1001',
            'article' => 'Laptop',
            'current_location_id' => null,
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'assigned_division_id' => 2,
            'assigned_section_id' => 21,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        PqsRecord::query()->create([
            'property_no' => 'PQS-1002',
            'article' => 'Monitor',
            'current_location_id' => null,
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'assigned_division_id' => 2,
            'assigned_section_id' => 21,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        $response = $this->actingAs($actor)
            ->withSession([
                'account_id' => $actor->account_id,
                'role' => $actor->role,
            ])
            ->postJson('/inventory/pqs/turnover', [
                'employee_id' => $employee->employee_id,
                'stockroom_location_id' => $stockroom->location_id,
                'remarks' => 'Resignation turnover',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.summary.total_assets', 2)
            ->assertJsonPath('data.summary.eligible_assets', 2)
            ->assertJsonPath('data.batch_reference', fn ($value) => is_string($value) && $value !== '');

        $this->assertDatabaseHas('pqs', [
            'property_no' => 'PQS-1001',
            'current_location_id' => $stockroom->location_id,
            'current_custodian_employee_id' => null,
            'accountable_officer_id' => null,
            'asset_status' => PqsRecord::STATUS_TRANSFERRED,
        ]);

        $this->assertDatabaseHas('pqs', [
            'property_no' => 'PQS-1002',
            'current_location_id' => $stockroom->location_id,
            'current_custodian_employee_id' => null,
            'accountable_officer_id' => null,
            'asset_status' => PqsRecord::STATUS_TRANSFERRED,
        ]);

        $this->assertSame(2, AssetMovement::query()->count());

        $batchReference = $response->json('data.batch_reference');

        $this->assertNotEmpty($batchReference);
        $this->assertSame(2, AssetMovement::query()->where('source_table', 'pqs_turnover_batch')->where('source_record_id', $batchReference)->count());
        $this->assertSame(1, Notification::query()->where('table_name', 'asset_movements')->where('record_id', $batchReference)->count());
    }

    public function test_turnover_rejects_invalid_location(): void
    {
        $actor = Account::query()->create([
            'username' => 'turnover.user.2',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $response = $this->actingAs($actor)
            ->withSession([
                'account_id' => $actor->account_id,
                'role' => $actor->role,
            ])
            ->postJson('/inventory/pqs/turnover', [
                'employee_id' => 'EMP-404',
                'stockroom_location_id' => 99999,
                'remarks' => 'Invalid request',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['employee_id', 'stockroom_location_id']);
    }

    public function test_turnover_blocks_unauthorized_role(): void
    {
        $actor = Account::query()->create([
            'username' => 'turnover.user.3',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        $response = $this->actingAs($actor)
            ->withSession([
                'account_id' => $actor->account_id,
                'role' => $actor->role,
            ])
            ->postJson('/inventory/pqs/turnover', [
                'employee_id' => 'EMP-404',
                'stockroom_location_id' => 1,
                'remarks' => 'Unauthorized',
            ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors('unauthorized');
    }

    public function test_turnover_returns_error_when_employee_has_no_assets(): void
    {
        $actor = Account::query()->create([
            'username' => 'turnover.user.4',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $employee = Employee::query()->create([
            'employee_id' => 'EMP-9002',
            'first_name' => 'No',
            'last_name' => 'Assets',
            'account_id' => null,
        ]);

        $stockroom = PhysicalLocation::query()->create([
            'location_name' => 'Secondary Stockroom',
            'location_code' => 'STK-02',
            'location_type' => 'storage',
            'is_active' => true,
        ]);

        $response = $this->actingAs($actor)
            ->withSession([
                'account_id' => $actor->account_id,
                'role' => $actor->role,
            ])
            ->postJson('/inventory/pqs/turnover', [
                'employee_id' => $employee->employee_id,
                'stockroom_location_id' => $stockroom->location_id,
                'remarks' => 'No assets',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error');
    }

    public function test_turnover_rolls_back_if_an_asset_save_fails(): void
    {
        $actor = Account::query()->create([
            'username' => 'turnover.user.5',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $employee = Employee::query()->create([
            'employee_id' => 'EMP-9003',
            'first_name' => 'Rollback',
            'last_name' => 'Case',
            'account_id' => null,
        ]);

        $stockroom = PhysicalLocation::query()->create([
            'location_name' => 'Rollback Stockroom',
            'location_code' => 'STK-03',
            'location_type' => 'storage',
            'is_active' => true,
        ]);

        PqsRecord::query()->create([
            'property_no' => 'PQS-2001',
            'article' => 'Keyboard',
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        PqsRecord::query()->create([
            'property_no' => 'PQS-2002',
            'article' => 'Mouse',
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        PqsRecord::saving(function (PqsRecord $record): void {
            if ($record->property_no === 'PQS-2002') {
                throw new RuntimeException('Forced failure during turnover.');
            }
        });

        $response = $this->actingAs($actor)
            ->withSession([
                'account_id' => $actor->account_id,
                'role' => $actor->role,
            ])
            ->postJson('/inventory/pqs/turnover', [
                'employee_id' => $employee->employee_id,
                'stockroom_location_id' => $stockroom->location_id,
                'remarks' => 'Force rollback',
            ]);

        $response->assertStatus(500);

        $this->assertDatabaseHas('pqs', [
            'property_no' => 'PQS-2001',
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        $this->assertDatabaseHas('pqs', [
            'property_no' => 'PQS-2002',
            'current_custodian_employee_id' => $employee->employee_id,
            'accountable_officer_id' => $employee->employee_id,
            'asset_status' => PqsRecord::STATUS_ACTIVE,
        ]);

        $this->assertSame(0, AssetMovement::query()->count());
    }
}
