# Custodian UI Redesign Playbook

Use this document as the single attachment when requesting future UI updates.
It defines a repeatable, industry-standard structure for redesign work while protecting existing business logic.

## 1. Objective
- Deliver modern, consistent, task-focused interfaces for Custodian workflows.
- Preserve all backend logic, API contracts, and role-based permissions.
- Improve readability, action clarity, and operational speed.

## 2. Non-Negotiable Guardrails
- Do not change business rules, status transitions, or validation logic.
- Do not change route names, controller endpoints, payload keys, or auth checks.
- Do not break export actions (PDF/Excel) or modal submit flows.
- Do not introduce full-page refresh for table interactions that are currently async.

## 3. Design System Baseline

### 3.1 Visual Direction
- Theme: professional procurement style with emerald accents.
- Surface hierarchy:
  - Hero banner for context and KPIs.
  - Main card for filters + table.
  - Subtle borders, rounded corners, soft shadow depth.
- Typography:
  - Compact and legible for high-density operational data.
  - Clear weight hierarchy for titles, labels, metadata, and values.

### 3.2 Layout Pattern
- Header/Hero:
  - Module tag, page title, one-line purpose.
  - KPI cards for the module's real operational states.
- Controls row:
  - Pill tabs (state filters), search, date range, apply filter, exports.
- Data section:
  - Status counters, table, row-level actions, footer summary, pagination.

### 3.3 Interaction Pattern
- Search and filters update data asynchronously.
- Tab changes update results without full page reload.
- Pagination updates table in place (no hard navigation).
- Loading, empty, and error states are explicit and user-friendly.

## 4. Behavior Contract (Required)

### 4.1 Filtering
- Keep hidden state input synced with active tab.
- Debounce search input.
- Date filters apply only when explicitly confirmed (Apply button).

### 4.2 Pagination
- Display count per page: 5 rows by default unless module-specific override.
- Keep pagination visible for non-empty result sets.
- Prev/Next and page number clicks must not refresh the entire page.
- Keep browser history state for current page where practical.

### 4.3 Table Rendering
- Use consistent table head style across Custodian pages.
- Animate row reveals lightly (fast, subtle, no motion overload).
- Maintain action button hierarchy:
  - Primary action: solid filled.
  - Secondary action: bordered neutral.

### 4.4 Feedback States
- Loading row in tbody while data is being fetched.
- Empty state with clear guidance.
- Error state with recovery hint.
- Toast notifications positioned consistently by module requirement.

## 5. KPI Rules
- KPI labels must reflect the same states used by tabs and table filters.
- KPI counts must come from the same data model as table logic.
- Avoid mixed-domain KPIs (example: document issuance metrics on assignment-only pages).

## 6. Accessibility and UX Quality
- Keyboard focus states must be visible.
- Sufficient contrast for text and controls.
- Click/tap targets should be usable on desktop and mobile.
- Avoid relying on color alone for state meaning (add text/icons).

## 7. Performance and Reliability
- Do not duplicate JS variable declarations or event bindings.
- Rebind delegated handlers safely after async updates.
- Keep DOM updates scoped to affected regions.
- Rebuild assets after JS/CSS changes.

## 8. Implementation Sequence (Industry Standard Workflow)
1. Audit existing page behavior and map non-functional regressions to avoid.
2. Update Blade structure and classes first (visual shell).
3. Update JS behavior next (state sync, async fetch, pagination, tabs).
4. Validate backend counts if KPI semantics changed.
5. Build assets and clear view cache.
6. Run smoke checks for search, tabs, date filter, pagination, and primary actions.

## 9. Definition of Done
- Visual parity with established Custodian procurement style.
- No regressions in logic, endpoints, permissions, or exports.
- Pagination and filtering are async and stable.
- KPIs are semantically correct for the current module.
- Build succeeds with no JS runtime errors.

## 10. Reusable Request Template
Copy and use this block for future requests:

```md
Apply the Custodian UI Redesign Playbook in docs/design/custodian-ui-redesign-playbook.md.

Requirements:
- Keep business logic and endpoints unchanged.
- Apply emerald procurement visual system and component hierarchy.
- Keep filters, tabs, and pagination async (no full-page refresh).
- Default pagination: 5 rows per page unless existing module requires otherwise.
- Align KPI labels/counts with actual table filter states.
- Rebuild assets and clear view cache after implementation.
- Provide a short change log with updated files.
```

## 11. Module-Specific Override Section (Optional)
When attaching this file, add module overrides below:
- Page: [module/page name]
- KPI labels required:
- Default tab required:
- Row actions required:
- Pagination override (if not 5):
- Toast placement (if module-specific):
