---
description: Strict prompt for implementing secure bulk employee asset turnover in the Laravel + Tailwind inventory system
---

Act as a senior Laravel engineer. Implement a secure, production-ready bulk Employee Asset Turnover feature for this repository.

Core system rules:
- Use the existing asset_movements ledger as the only movement history source.
- Do not create duplicate turnover history tables unless absolutely required.
- Treat turnover as a specialized batch asset movement flow.
- Keep procurement documents separate from live custody tracking.
- Keep physical location on PQS assets, not on employees.
- Keep employee organizational data as section and division context only.
- Block disposed or non-transferable assets.
- Validate all inputs server-side.
- Authorize all actions through the existing policy and role structure.
- Use one database transaction for the full turnover batch.
- Fail closed on invalid employees, invalid locations, or empty asset sets.

Implementation expectations:
- Reuse current models, policies, controllers, routes, and Tailwind views whenever possible.
- Prefer minimal, backward-compatible changes.
- Do not refactor unrelated code.
- Do not change procurement workflows unless the task explicitly requires it.
- Do not add a physical location field to the employee table as a source of truth.
- Prefer current_location_id and current_custodian_employee_id patterns already used in PQS.
- Add or modify schema only when a missing field is truly required.

When implementing the feature, inspect the current code first and then make changes in this order:
1. Domain rules and authorization
2. Controller logic
3. Routes
4. Tailwind Blade UI
5. Feature tests

Functional requirements:
- Accept employee_id, stockroom_location_id, optional effective_at, and remarks.
- Load all PQS assets currently assigned to the selected employee.
- Confirm the destination location exists, is active, and has location_type = storage.
- For each affected asset, create one asset_movements row and update the live PQS record.
- Update pqs.current_location_id.
- Clear or reassign pqs.current_custodian_employee_id according to the chosen turnover rule.
- Update pqs.asset_status.
- Update pqs.last_movement_at.
- Return a structured summary of affected, skipped, and failed assets.
- Preserve existing single-asset transfer and reporting behavior.

UI requirements:
- Build a clean, responsive Tailwind form.
- Include employee selector, stockroom location selector, optional effective date, remarks textarea, submit button, cancel action, inline validation messages, success and error banners, and optional preview.
- Keep the design aligned with the existing PQS UI.

Output expected:
- controller method
- any model helper needed
- route additions
- Blade view or modal updates
- tests

Quality bar:
- Production-ready Laravel code
- Transaction-safe
- Secure validation and authorization
- Clear audit trail
- Minimal schema changes
- No duplicate movement storage
- Consistent with existing repository conventions
