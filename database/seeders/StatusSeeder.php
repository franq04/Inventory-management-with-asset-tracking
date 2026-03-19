<?php

namespace Database\Seeders;

use App\Models\Status;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    /**
     * Seed canonical status rows used across PR/PO/inspection workflows.
     */
    public function run(): void
    {
        $statuses = [
            // Purchase request
            ['status_id' => Status::PR_DRAFT, 'status_name' => 'Draft', 'status_code' => 'PR_DRAFT', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_FOR_RECOMMENDATION, 'status_name' => 'For Recommendation', 'status_code' => 'PR_FOR_RECOMMENDATION', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_RECOMMENDED, 'status_name' => 'Recommended', 'status_code' => 'PR_RECOMMENDED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_FOR_APPROVAL, 'status_name' => 'For Approval', 'status_code' => 'PR_FOR_APPROVAL', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_APPROVED, 'status_name' => 'Approved', 'status_code' => 'PR_APPROVED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_CANCELLED, 'status_name' => 'Cancelled', 'status_code' => 'PR_CANCELLED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_PO_CREATED, 'status_name' => 'PO Created', 'status_code' => 'PR_PO_CREATED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_PO_SENT_TO_SUPPLIER, 'status_name' => 'PO Sent to Supplier', 'status_code' => 'PR_PO_SENT_TO_SUPPLIER', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_PARTIALLY_DELIVERED, 'status_name' => 'Partially Delivered', 'status_code' => 'PR_PARTIALLY_DELIVERED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_DELIVERED_PENDING_INSPECTION, 'status_name' => 'Delivered Pending Inspection', 'status_code' => 'PR_DELIVERED_PENDING_INSPECTION', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],
            ['status_id' => Status::PR_INSPECTION_COMPLETED, 'status_name' => 'Inspection Completed', 'status_code' => 'PR_INSPECTION_COMPLETED', 'status_scope' => Status::SCOPE_PURCHASE_REQUEST],

            // Purchase order
            ['status_id' => Status::PO_CREATED, 'status_name' => 'PO Created', 'status_code' => 'PO_CREATED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],
            ['status_id' => Status::PO_SENT_TO_SUPPLIER, 'status_name' => 'PO Sent to Supplier', 'status_code' => 'PO_SENT_TO_SUPPLIER', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],
            ['status_id' => Status::PO_PARTIALLY_DELIVERED, 'status_name' => 'Partially Delivered', 'status_code' => 'PO_PARTIALLY_DELIVERED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],
            ['status_id' => Status::PO_DELIVERED_PENDING_INSPECTION, 'status_name' => 'Delivered Pending Inspection', 'status_code' => 'PO_DELIVERED_PENDING_INSPECTION', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],
            ['status_id' => Status::PO_CLOSED, 'status_name' => 'Closed', 'status_code' => 'PO_CLOSED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],
            ['status_id' => Status::PO_CANCELLED, 'status_name' => 'Cancelled', 'status_code' => 'PO_CANCELLED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER],

            // Purchase order item / inspection
            ['status_id' => Status::ITEM_PENDING_INSPECTION, 'status_name' => 'Pending Inspection', 'status_code' => 'ITEM_PENDING_INSPECTION', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
            ['status_id' => Status::ITEM_ACCEPTED, 'status_name' => 'Accepted', 'status_code' => 'ITEM_ACCEPTED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
            ['status_id' => Status::ITEM_DEFECTIVE, 'status_name' => 'Defective', 'status_code' => 'ITEM_DEFECTIVE', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
            ['status_id' => Status::ITEM_RETURNED, 'status_name' => 'Returned', 'status_code' => 'ITEM_RETURNED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
            ['status_id' => Status::ITEM_REPLACED, 'status_name' => 'Replaced', 'status_code' => 'ITEM_REPLACED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
            ['status_id' => Status::ITEM_RECORDED, 'status_name' => 'Recorded', 'status_code' => 'ITEM_RECORDED', 'status_scope' => Status::SCOPE_PURCHASE_ORDER_ITEM],
        ];

        foreach ($statuses as $status) {
            DB::table('statuses')->updateOrInsert(
                ['status_id' => $status['status_id']],
                [
                    'status_name' => $status['status_name'],
                    'status_code' => $status['status_code'],
                    'status_scope' => $status['status_scope'],
                    'remarks' => null,
                    'updated_at' => now(),
                    'created_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
                ]
            );
        }
    }
}
