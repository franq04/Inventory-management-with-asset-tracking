# Custodian Inspection and Acceptance Redesign Checklist

Use this checklist whenever updating the Inspection and Acceptance page design.

## Scope Guardrails
- [ ] Keep Export PDF button behavior unchanged.
- [ ] Keep Export Excel button behavior unchanged.
- [ ] Do not alter inspection business rules or status transitions.
- [ ] Do not alter modal submission endpoints or payload structure.

## Visual System Alignment
- [ ] Hero uses procurement-style gradient banner.
- [ ] Hero includes summary KPIs (Total Queue, Visible Rows, Active Status).
- [ ] Main content card uses rounded corners and soft shadow.
- [ ] Color palette remains consistent with emerald procurement theme.
- [ ] Typography remains compact and legible for operations users.

## Status Tabs
- [ ] Tabs are pill-style controls matching Custodian Purchase Request design.
- [ ] Active tab has strong contrast and elevated visual treatment.
- [ ] Inactive tabs have neutral styling with clear hover affordance.
- [ ] Tab counts are visible and readable in both active/inactive states.

## Filters and Controls
- [ ] Search, date range, and filter apply action are grouped consistently.
- [ ] Layout remains responsive (desktop row, mobile stack).
- [ ] Focus states are visible for keyboard users.
- [ ] Export controls remain in original visual treatment.

## Table and Panels
- [ ] Panel header shows status title, description, and count badge.
- [ ] Table header matches procurement table style.
- [ ] Row hover state is subtle and consistent.
- [ ] Empty state messaging is clear and centered.
- [ ] Footer contains summary text and pagination controls.

## Pagination and Navigation Behavior
- [ ] Pagination uses vendor.pagination.procurement template.
- [ ] Pagination is AJAX-based (no full-page reload).
- [ ] Browser history updates during pagination (pushState).
- [ ] Back/forward navigation restores list state (popstate).
- [ ] A loading skeleton appears during asynchronous page swaps.

## Async Modal Save Behavior
- [ ] Row-level edits support optimistic UI updates (row-only patch, no full-table reload).
- [ ] Failed async saves cleanly roll back optimistic row changes.
- [ ] Save actions include CSRF protection and server-side validation handling.
- [ ] Success and error feedback are surfaced through non-blocking toast messaging.

## Tab State and URL Behavior
- [ ] Active tab is synced to URL query parameter (tab).
- [ ] Loading the page with tab query restores correct active tab.
- [ ] Switching tabs updates URL without hard reload.
- [ ] Active status KPI label updates when tab changes.

## QA Verification
- [ ] Build assets successfully via Vite.
- [ ] No Blade errors in inspection view.
- [ ] No JS errors in inspection module.
- [ ] Verify tabs, search, date filter, and pagination together.
- [ ] Verify Inspect / Update and View Details actions still work.

## Sign-off
- [ ] UI review complete.
- [ ] Behavior parity with Custodian Purchase Request confirmed.
- [ ] No regressions on export actions confirmed.
