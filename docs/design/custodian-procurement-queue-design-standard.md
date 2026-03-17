# Custodian Procurement Queue Design Standard

## Objective

Apply the same industry-standard procurement dashboard treatment used in the employee purchase requests page to the custodian procurement queue while preserving the established green institutional theme.

## Visual Direction

- Keep the deep green brand base anchored on `#1a3a2d`
- Use restrained gold highlights anchored on `#f9bf0f`
- Maintain a formal administrative tone suitable for procurement review and queue management

## Required Page Structure

1. Branded summary header
   Show the page title, concise operational context, and a compact KPI row for queue totals and visible value.

2. Filter and tab control shell
   Group status tabs, search, and export actions inside one elevated panel so the queue feels like one coordinated workspace.

3. Structured data table
   Use a rounded table container with a soft tinted header, clearer hover treatment, and deliberate action buttons.

4. Consistent pagination footer
   Use the themed pagination component even when there is only one page so the layout remains stable and predictable.

## Component Standards

### Header Card

- Use a restrained green gradient background
- Present three KPI cards maximum
- Keep supporting text short and operational

### Tabs

- Active state: solid green with white text
- Inactive state: off-white or pale neutral with subtle borders
- Count badges should remain readable in both active and inactive states

### Search And Exports

- Search should be visually prominent and left-aligned
- Export actions should remain secondary but consistent with page controls
- Rounded corners should match the rest of the module

### Table

- Prioritize procurement fields: PR number, requester, division or section, status, total cost, submitted date, and review action
- Monetary values should use a soft chip treatment for fast scanning
- Action buttons should follow the same green outline-fill system used elsewhere in the module

### Pagination

- Show page summary on the left
- Use rounded page pills and separate `Prev` and `Next` actions
- Preserve the same custom pagination template for consistency across procurement pages

## Implementation Notes

- Use the shared pagination view at `vendor.pagination.procurement`
- Keep Tailwind utility styling in Blade for page-specific presentation
- Rebuild with `npm run build` after template changes
- Reuse the same visual language across employee, custodian, and BAC procurement pages where possible