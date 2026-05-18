# Asset Tracking Lifecycle Guide

This guide explains where asset tracking starts, how automatic location assignment works, and what happens when turnover occurs after a PQS record is created.

## Scope
- Module: Custodian Inventory Assignment and Asset Movement tracking
- Records involved:
  - inspection_report_items
  - pqs
  - asset_movements
  - physical_locations

## Where Asset Tracking Starts
Asset tracking begins at the moment a PQS record is created from an accepted inspection item.

At that point, the system writes:
1. A new row in pqs (the asset master state)
2. A new row in asset_movements with movement_type = initial_assignment (the asset timeline)

This is the first immutable movement event for that property number.

## Automated Initial Location Decisioning
When Create PQS is submitted, the system resolves initial_location_id using this order:
1. Use the location explicitly selected in the form, if provided.
2. Otherwise auto-suggest from the accountable employee section mapping.
3. If no section match exists, fallback to accountable employee division mapping.
4. If no mapping exists, leave location null and allow manual assignment later.

Why this order:
- It respects user intent first.
- It still supports automation when no manual selection is made.
- It avoids wrong defaults when organizational mapping is incomplete.

## How the Initial Employee Is Determined
The initial employee is the accountable officer from the source purchase request requester employee.

On PQS create, the system writes:
- pqs.current_custodian_employee_id = accountable officer
- pqs.assigned_division_id and pqs.assigned_section_id from that employee
- asset_movements.to_custodian_employee_id, to_division_id, to_section_id for initial_assignment

## Turnover Process (After PQS Creation)
Turnover is a transfer event and should always create a new asset_movements row.

Recommended turnover write pattern:
1. Read current pqs state (location, employee, division, section).
2. Validate target assignment (employee/location/division/section) and permissions.
3. Insert asset_movements row:
- from_* fields = previous pqs state
- to_* fields = target assignment
- movement_type = transfer or relocation
- reason_code and remarks required
- effective_at and recorded_by required
4. Update pqs current state to target assignment.
5. Update pqs.last_movement_at.
6. Emit notification and audit log.

This preserves both:
- Current truth (pqs)
- Full history (asset_movements)

## Quick Transfer vs Bulk Employee Turnover

### What is Quick Transfer
Quick Transfer is a single-asset movement action from one PQS record view.

Use it when you need to move one specific property number to a new assignment.

Characteristics:
- Scope: one asset at a time
- Endpoint: pqs/{property_no}/movements
- Allowed movement types: transfer, relocation, inventory_correction, maintenance_out, maintenance_in
- Target flexibility: location, employee, division, section (any valid combination)
- Maintenance behavior: maintenance_out sets asset status to maintenance (under maintenance) or for_repair (unserviceable) based on reason_code; maintenance_in sets it back to active
- Notification behavior: sends assignment notification when a target employee is set

### What is Bulk Employee Turnover
Bulk Employee Turnover is a batch action for all transferable assets currently tied to one employee.

Use it when an employee resigns, rotates out, or must surrender all issued assets.

Characteristics:
- Scope: multiple assets in one transaction
- Endpoint: pqs/turnover
- Fixed movement semantics: movement_type = transfer, reason_code = employee_turnover
- Target restriction: stockroom location only (active location_type = storage)
- Custody outcome: clears current custodian and accountable officer from moved assets
- Status outcome: marks moved assets as transferred
- Batch traceability: one batch reference links all generated movement rows

### Decision Rule (When to use which)
1. Use Quick Transfer if only one asset is being reassigned or corrected.
2. Use Quick Transfer for maintenance in/out events.
3. Use Bulk Employee Turnover if an employee has multiple assets that must be surrendered at once.
4. Use Bulk Employee Turnover only when destination is a stockroom.

## Step-by-Step: Quick Transfer
1. Open PQS list and click Quick Transfer (or open View then go to Transfer Asset).
2. Confirm you are on the correct property number and current assignment.
3. Select Movement Type.
4. Set target fields as needed:
- Target Location
- Target Employee
- Target Division
- Target Section
5. Optional: enter Reason Code and Remarks.
6. Click Save Movement.
7. System validates:
- at least one target changed
- section/division consistency
- asset is not disposed/lost
8. On success, system writes one asset_movements row and updates pqs current assignment.
9. Verify success in Movement Timeline and Current Assignment panel.

## Step-by-Step: Bulk Employee Turnover
1. Open a PQS record modal and scroll to Bulk Employee Turnover.
2. Select Resigning Employee.
3. Select Stockroom Location (must be active and type storage).
4. Optional: set Effective Date and Remarks.
5. Click Process Turnover.
6. System gathers all assets where the employee is current custodian or accountable officer.
7. System blocks processing if any matched asset is not transferable (disposed/lost).
8. System runs one transaction for all eligible assets:
- inserts movement rows with transfer + employee_turnover
- moves each asset to stockroom
- clears custodian/accountable officer
- sets asset status to transferred
9. System returns batch reference and success summary.
10. Verify updates in Movement Timeline and reconciliation widgets.

## What happens after turnover
1. The asset remains traceable by property number through movement history.
2. Custody is unassigned until the next issuance/transfer action.
3. The next assignment should be done through Quick Transfer (or issuance workflow) to a target employee/location.

## Industry-Standard Controls
1. Referential integrity
- physical_locations links to sections and divisions through foreign keys.
- pqs.current_location_id and asset_movements location references link to physical_locations.

2. Defensive migration behavior
- Before adding foreign keys, orphan location references are normalized to null.
- This avoids migration failure in existing live data.

3. Principle of least surprise
- UI auto-displays the inferred initial location.
- User can still override before save.

4. Auditability
- Each ownership/location change must append one movement row, not overwrite history.

## Operational Checklist
1. Master data readiness
- Ensure each section/division used by requesters has at least one active physical location.

2. During PQS creation
- Confirm Initial Employee is correct.
- Confirm Initial Location is auto-selected or manually chosen.

3. During turnover
- Confirm target employee/location is valid.
- Require reason and remarks.
- Verify both pqs and asset_movements are updated in one transaction.

## Failure and Fallback Cases
- No section/division mapping for a location:
  - PQS can still be created.
  - Location stays null until manually assigned.
- Inactive or deleted location references:
  - Validation blocks inactive location on new input.
  - Foreign keys + null-on-delete prevent dangling references.

## Practical Summary
- Asset tracking starts at PQS creation.
- Initial movement is always initial_assignment.
- Every turnover appends a movement and updates current pqs state.
- Automation is default; manual override is allowed; history remains complete.
