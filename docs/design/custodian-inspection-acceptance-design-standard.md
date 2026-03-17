# Custodian Inspection and Acceptance Design Standard

This document captures the upgraded design and interaction pattern for the Custodian Inspection and Acceptance page.

## Objective
- Match the visual language and UX behavior of the Custodian Purchase Request queue.
- Keep action density high while preserving readability for audit and inspection workflows.
- Preserve existing Export PDF and Export Excel control behavior and styling.

## Visual Direction
- Use a procurement dashboard style with:
  - A gradient hero summary card
  - Rounded glass-like content cards
  - Consistent emerald-focused brand accents
  - Neutral table surfaces with subtle hover states
- Use compact but legible typography suitable for operations screens.

## Page Composition
1. Hero summary section
- Gradient background with decorative overlays.
- Three KPI cards:
  - Total Queue
  - Visible Rows
  - Active Status

2. Filter and tab section
- Tab pills mirror the procurement queue style:
  - Active state: dark emerald background with soft shadow
  - Inactive state: neutral background with hover elevation
- Keep the search and date filters in one row on desktop.
- Keep Export PDF and Export Excel controls unchanged.

3. Data panels by status
- One panel per status segment:
  - Pending Inspection
  - Accepted / Recorded
  - Defective
  - Returned / Replacement
- Each panel includes:
  - Panel header with segment badge count
  - Table with consistent spacing and column rhythm
  - Footer with summary text and procurement pagination component

## Interaction Behavior
- Tabs switch status panels without page reload.
- Pagination is AJAX-driven and updates only the inspection page container.
- Browser history is preserved via pushState and popstate handling.
- Search and date filter values persist across AJAX pagination transitions.
- Existing inspection modal open/edit flow remains unchanged.

## Pagination Pattern
- Use the shared template:
  - vendor.pagination.procurement
- Scope pagination interception to the inspection page root container.
- Prevent default link navigation and fetch the next page via AJAX.
- Replace only the inspection root section in the DOM.

## Accessibility and Usability
- Keep button labels explicit (Inspect / Update, View Details).
- Preserve visible focus styles on search and date fields.
- Keep text contrast strong on all colored badges and tab states.
- Maintain mobile-safe wrapping for tabs and control rows.

## Non-negotiables
- Do not alter Export PDF button design or behavior.
- Do not alter Export Excel button design or behavior.
- Do not change inspection business logic or status transition logic.

## Reuse Checklist (for future redesign tasks)
- Keep hero + KPI card structure.
- Reuse pill-style status tabs with active/inactive class toggling.
- Keep procurement table shell and footer pagination treatment.
- Keep AJAX pagination with stateful history.
- Validate no regression in modal inspection flows.
