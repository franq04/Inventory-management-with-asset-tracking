<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Account;
use App\Models\PurchaseRequest;
use App\Models\Status;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class PurchaseRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create minimal tables required for the test (in-memory)
        if (! Schema::hasTable('statuses')) {
            Schema::create('statuses', function (Blueprint $table) {
                $table->integer('status_id')->primary();
                $table->string('status_code')->nullable();
                $table->string('status_scope')->nullable();
                $table->string('status_name')->nullable();
            });
        }

        if (! Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->integer('account_id')->primary();
                $table->string('username')->unique();
                $table->string('password');
                $table->string('role')->nullable();
            });
        }

        if (! Schema::hasTable('purchase_requests')) {
            Schema::create('purchase_requests', function (Blueprint $table) {
                $table->string('pr_no')->primary();
                $table->integer('account_id');
                $table->integer('status_id');
                $table->integer('division_id')->nullable();
                $table->integer('section_id')->nullable();
                $table->text('purpose')->nullable();
                $table->decimal('total_estimated_cost', 12, 2)->default(0);
                $table->decimal('funds_available', 12, 2)->nullable();
                $table->integer('approved_by')->nullable();
                $table->dateTime('approved_at')->nullable();
                $table->text('approval_remarks')->nullable();
                $table->integer('reviewed_by')->nullable();
                $table->timestamps(0);
            });
        }

        if (! Schema::hasTable('purchase_request_items')) {
            Schema::create('purchase_request_items', function (Blueprint $table) {
                $table->increments('pri_id');
                $table->string('pr_no');
                $table->text('item_description')->nullable();
                $table->string('fulfillment_status')->nullable();
                $table->string('employee_decision')->nullable();
                $table->date('employee_wait_until')->nullable();
                $table->text('alternate_description')->nullable();
                $table->decimal('estimated_unit_cost', 12, 2)->default(0);
            });
        }

        if (! Schema::hasTable('status_history')) {
            Schema::create('status_history', function (Blueprint $table) {
                $table->increments('history_id');
                $table->string('table_name');
                $table->string('record_id');
                $table->integer('old_status_id')->nullable();
                $table->integer('new_status_id')->nullable();
                $table->integer('changed_by')->nullable();
                $table->text('remarks')->nullable();
                $table->dateTime('changed_at')->nullable();
            });
        }

        if (! Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->increments('log_id');
                $table->integer('account_id')->nullable();
                $table->string('table_name')->nullable();
                $table->string('action')->nullable();
                $table->text('description')->nullable();
                $table->dateTime('log_time')->nullable();
            });
        }

        if (! Schema::hasTable('notifications')) {
            Schema::create('notifications', function (Blueprint $table) {
                $table->increments('notification_id');
                $table->integer('recipient_id')->nullable();
                $table->integer('sender_id')->nullable();
                $table->string('table_name')->nullable();
                $table->string('record_id')->nullable();
                $table->text('message')->nullable();
                $table->string('type')->nullable();
                $table->boolean('is_read')->default(false);
                $table->dateTime('created_at')->nullable();
            });
        }

        // Ensure statuses exist
        Status::insert([
            ['status_id' => Status::PR_FOR_RECOMMENDATION, 'status_code' => 'PR-REC', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST, 'status_name' => 'For Recommendation'],
            ['status_id' => Status::PR_FOR_APPROVAL, 'status_code' => 'PR-APP', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST, 'status_name' => 'For Approval'],
            ['status_id' => Status::PR_APPROVED, 'status_code' => 'PR-APPR', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST, 'status_name' => 'Approved'],
        ]);
    }

    public function test_custodian_cannot_approve_their_own_purchase_request()
    {
        // Create a custodian account
        $custodian = Account::factory()->create(['role' => 'custodian']);

        // Create a purchase request authored by the custodian
        $pr = PurchaseRequest::create([
            'pr_no' => 'PR-TEST-001',
            'account_id' => $custodian->account_id,
            'status_id' => Status::PR_FOR_APPROVAL,
            'division_id' => 1,
            'section_id' => 1,
            'purpose' => 'Test',
            'total_estimated_cost' => 1000,
        ]);

        $response = $this->actingAs($custodian)
            ->withSession(['role' => 'custodian', 'account_id' => $custodian->account_id])
            ->postJson(route('custodian.requests.update-status', ['purchase_request' => $pr->pr_no]), [
                'status_id' => Status::PR_APPROVED,
                'remarks' => 'Auto-approve',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('status_id');
    }

    public function test_other_user_can_approve_a_custodian_request()
    {
        $custodian = Account::factory()->create(['role' => 'custodian']);
        $approver = Account::factory()->create(['role' => 'bac']);

        $pr = PurchaseRequest::create([
            'pr_no' => 'PR-TEST-002',
            'account_id' => $custodian->account_id,
            'status_id' => Status::PR_FOR_APPROVAL,
            'division_id' => 1,
            'section_id' => 1,
            'purpose' => 'Test approval',
            'total_estimated_cost' => 2000,
        ]);

        $response = $this->actingAs($approver)
            ->withSession(['role' => 'bac', 'account_id' => $approver->account_id])
            ->postJson(route('custodian.requests.update-status', ['purchase_request' => $pr->pr_no]), [
                'status_id' => Status::PR_APPROVED,
                'remarks' => 'Approved by BAC',
                'funds_available' => 2000,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('purchase_requests', [
            'pr_no' => $pr->pr_no,
            'status_id' => Status::PR_APPROVED,
        ]);
    }
}
