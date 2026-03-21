<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\InspectionReport;
use App\Models\InspectionReportItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequest;
use App\Models\Status;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PurchaseOrderReceivingTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTables();
    }

    protected function createTables(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('status_history');
        Schema::dropIfExists('inspection_report_items');
        Schema::dropIfExists('inspection_reports');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('purchase_requests');
        Schema::dropIfExists('accounts');

        Schema::create('accounts', function (Blueprint $table): void {
            $table->increments('account_id');
            $table->string('username')->unique();
            $table->string('password');
            $table->string('role');
        });

        Schema::create('purchase_requests', function (Blueprint $table): void {
            $table->string('pr_no')->primary();
            $table->integer('account_id')->nullable();
            $table->integer('status_id');
            $table->string('purpose')->nullable();
            $table->decimal('funds_available', 12, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table): void {
            $table->string('po_no')->primary();
            $table->string('pr_no')->nullable();
            $table->integer('status_id');
            $table->integer('ordered_by')->nullable();
            $table->string('fund_cluster')->nullable();
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table): void {
            $table->increments('poi_id');
            $table->string('po_no');
            $table->string('item_description')->nullable();
            $table->integer('quantity')->default(1);
            $table->string('unit')->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0);
            $table->timestamp('received_at')->nullable();
            $table->integer('received_by')->nullable();
            $table->string('receiving_note')->nullable();
            $table->string('fulfillment_status')->nullable();
            $table->string('alternate_description')->nullable();
            $table->string('employee_decision')->nullable();
            $table->timestamp('employee_decided_at')->nullable();
            $table->timestamp('employee_wait_until')->nullable();
            $table->text('employee_wait_note')->nullable();
            $table->string('inspection_status_id')->nullable();
            $table->text('inspection_remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('inspection_reports', function (Blueprint $table): void {
            $table->string('ia_no')->primary();
            $table->string('po_no');
            $table->date('inspection_date')->nullable();
            $table->date('accepted_date')->nullable();
            $table->string('invoice_no')->nullable();
            $table->date('invoice_date')->nullable();
            $table->text('remarks')->nullable();
            $table->integer('overall_status_id')->nullable();
        });

        Schema::create('inspection_report_items', function (Blueprint $table): void {
            $table->increments('ia_item_id');
            $table->string('ia_no');
            $table->unsignedInteger('po_item_id');
            $table->integer('quantity_delivered')->nullable();
            $table->integer('quantity_accepted')->nullable();
            $table->integer('quantity_rejected')->nullable();
            $table->integer('inspection_status_id')->nullable();
            $table->text('inspection_remarks')->nullable();
            $table->string('property_no')->nullable();
            $table->date('warranty_expiration')->nullable();
        });

        Schema::create('status_history', function (Blueprint $table): void {
            $table->increments('history_id');
            $table->string('table_name');
            $table->string('record_id');
            $table->integer('old_status_id')->nullable();
            $table->integer('new_status_id')->nullable();
            $table->integer('changed_by')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamp('changed_at')->nullable();
        });

        Schema::create('audit_logs', function (Blueprint $table): void {
            $table->increments('log_id');
            $table->integer('account_id')->nullable();
            $table->string('table_name')->nullable();
            $table->string('action')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('log_time')->nullable();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->increments('notification_id');
            $table->integer('recipient_id')->nullable();
            $table->integer('sender_id')->nullable();
            $table->string('table_name')->nullable();
            $table->string('record_id')->nullable();
            $table->text('message')->nullable();
            $table->string('type')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('created_at')->nullable();
        });
    }

    public function test_final_receipt_creates_pending_inspection_record(): void
    {
        $custodian = Account::query()->create([
            'username' => 'custodian.user',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $requester = Account::query()->create([
            'username' => 'requester.user',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        PurchaseRequest::query()->create([
            'pr_no' => 'PR-TEST-100',
            'account_id' => $requester->account_id,
            'status_id' => Status::PR_APPROVED,
            'purpose' => 'Office equipment',
            'funds_available' => 1000,
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_no' => 'PO-TEST-100',
            'pr_no' => 'PR-TEST-100',
            'status_id' => Status::PO_SENT_TO_SUPPLIER,
            'ordered_by' => $custodian->account_id,
            'fund_cluster' => '156',
        ]);

        $item = PurchaseOrderItem::query()->create([
            'po_no' => $purchaseOrder->po_no,
            'item_description' => 'Desktop Computer',
            'quantity' => 1,
            'unit' => 'set',
            'unit_cost' => 21000,
            'fulfillment_status' => 'ordered',
        ]);

        $response = $this->actingAs($custodian)
            ->withSession([
                'account_id' => $custodian->account_id,
                'role' => $custodian->role,
            ])
            ->postJson('/custodian/purchase-orders/items/'.$item->poi_id.'/receive', [
                'receiving_note' => 'Delivered and logged',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('order_status', Status::PO_DELIVERED_PENDING_INSPECTION);

        $this->assertDatabaseHas('purchase_order_items', [
            'poi_id' => $item->poi_id,
            'received_by' => $custodian->account_id,
        ]);

        $this->assertDatabaseHas('purchase_orders', [
            'po_no' => $purchaseOrder->po_no,
            'status_id' => Status::PO_DELIVERED_PENDING_INSPECTION,
        ]);

        $this->assertDatabaseHas('inspection_reports', [
            'po_no' => $purchaseOrder->po_no,
            'overall_status_id' => Status::ITEM_PENDING_INSPECTION,
        ]);

        $this->assertDatabaseHas('inspection_report_items', [
            'po_item_id' => $item->poi_id,
            'inspection_status_id' => Status::ITEM_PENDING_INSPECTION,
            'quantity_delivered' => 1,
            'quantity_accepted' => 0,
            'quantity_rejected' => 0,
        ]);

        $this->assertSame(1, InspectionReport::query()->count());
        $this->assertSame(1, InspectionReportItem::query()->count());
    }

    public function test_update_item_does_not_downgrade_delivered_po_back_to_receiving(): void
    {
        $custodian = Account::query()->create([
            'username' => 'custodian.user.2',
            'password' => bcrypt('password'),
            'role' => 'custodian',
        ]);

        $requester = Account::query()->create([
            'username' => 'requester.user.2',
            'password' => bcrypt('password'),
            'role' => 'employee',
        ]);

        PurchaseRequest::query()->create([
            'pr_no' => 'PR-TEST-200',
            'account_id' => $requester->account_id,
            'status_id' => Status::PR_APPROVED,
            'purpose' => 'Office equipment',
            'funds_available' => 1000,
        ]);

        $purchaseOrder = PurchaseOrder::query()->create([
            'po_no' => 'PO-TEST-200',
            'pr_no' => 'PR-TEST-200',
            'status_id' => Status::PO_SENT_TO_SUPPLIER,
            'ordered_by' => $custodian->account_id,
            'fund_cluster' => '156',
        ]);

        $firstItem = PurchaseOrderItem::query()->create([
            'po_no' => $purchaseOrder->po_no,
            'item_description' => 'Printer',
            'quantity' => 1,
            'unit' => 'unit',
            'unit_cost' => 8500,
            'fulfillment_status' => 'ordered',
        ]);

        $secondItem = PurchaseOrderItem::query()->create([
            'po_no' => $purchaseOrder->po_no,
            'item_description' => 'Scanner',
            'quantity' => 1,
            'unit' => 'unit',
            'unit_cost' => 6500,
            'fulfillment_status' => 'ordered',
        ]);

        $receiveHeaders = [
            'account_id' => $custodian->account_id,
            'role' => $custodian->role,
        ];

        $this->actingAs($custodian)
            ->withSession($receiveHeaders)
            ->postJson('/custodian/purchase-orders/items/'.$firstItem->poi_id.'/receive', [
                'receiving_note' => 'First delivery received',
            ])
            ->assertStatus(200);

        $this->actingAs($custodian)
            ->withSession($receiveHeaders)
            ->postJson('/custodian/purchase-orders/items/'.$secondItem->poi_id.'/receive', [
                'receiving_note' => 'Second delivery received',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('purchase_orders', [
            'po_no' => $purchaseOrder->po_no,
            'status_id' => Status::PO_DELIVERED_PENDING_INSPECTION,
        ]);

        $this->actingAs($custodian)
            ->withSession($receiveHeaders)
            ->patchJson('/custodian/purchase-orders/items/'.$firstItem->poi_id, [
                'fulfillment_status' => 'unavailable',
                'unit_cost' => 0,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('purchase_orders', [
            'po_no' => $purchaseOrder->po_no,
            'status_id' => Status::PO_DELIVERED_PENDING_INSPECTION,
        ]);

        $this->assertSame(1, PurchaseOrder::query()->where('po_no', $purchaseOrder->po_no)->where('status_id', Status::PO_DELIVERED_PENDING_INSPECTION)->count());
    }
}