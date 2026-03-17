# Custodian Manage Accounts Redesign Checklist

Use this checklist when updating the Manage Accounts page UI and behavior.

## Scope Guardrails
- [ ] Keep account search and role filter query behavior unchanged.
- [ ] Keep Export PDF behavior unchanged.
- [ ] Keep Export Excel behavior unchanged.
- [ ] Do not alter account edit/update endpoints or payload contracts.

## Visual System Alignment
- [ ] Hero uses emerald procurement-style gradient.
- [ ] Hero includes access-control KPI cards.
- [ ] Main table card uses rounded corners and soft elevation.
- [ ] Typography is compact and readable for admin users.
- [ ] Color treatment follows established Custodian theme.

## Filters and Controls
- [ ] Search and role filter are grouped in one control row.
- [ ] Inputs have clear focus states.
- [ ] Controls remain responsive (desktop row, mobile stack).
- [ ] Records count updates after async operations.

## Table and Panels
- [ ] Table header matches procurement table style.
- [ ] Row hover state is subtle and consistent.
- [ ] Role badges remain visually distinct and readable.
- [ ] Empty state messaging is centered and clear.
- [ ] Pagination section uses procurement pagination template.

## Async Behavior Contract
- [ ] Search is debounced and updates in place (no full-page reload).
- [ ] Role filter updates in place (no full-page reload).
- [ ] Pagination updates in place and pushes browser history.
- [ ] Back/forward restores list state via popstate.
- [ ] Loading skeleton appears during async table refresh.
- [ ] Row reveal animation is fast and subtle.

## Side Insights Panels
- [ ] Role Distribution panel remains visible and readable.
- [ ] Recently Added accounts panel remains intact.
- [ ] Side panel spacing matches card rhythm of primary content.

## QA Verification
- [ ] `php artisan view:clear` runs cleanly.
- [ ] No Blade errors in Manage Accounts view.
- [ ] No JS runtime errors in search/filter/pagination flow.
- [ ] Export buttons still include active filter query params.

## Sign-off
- [ ] UI review completed.
- [ ] Async interaction parity confirmed.
- [ ] No regressions on account operations confirmed.
