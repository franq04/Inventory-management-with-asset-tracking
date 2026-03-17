# Purchase Requests Design Standard

## Objective

Create an industry-standard procurement dashboard experience for the purchase requests module while preserving the existing institutional theme:

- Primary brand color stays in the deep green family anchored on `#1a3a2d`
- Accent color stays in the gold family anchored on `#f9bf0f`
- The interface should feel official, operational, and credible rather than consumer-app styled

## Design Principles

1. Use strong information hierarchy.
   Pages should lead with the most important operational signals first: title, context, key metrics, and then the working table.

2. Keep interactions obvious and low-friction.
   Search, status filters, reset, create, and view actions should remain immediately discoverable with clear contrast and generous hit areas.

3. Favor structured surfaces over flat layouts.
   Use layered cards, restrained gradients, soft borders, and controlled shadows to make sections easier to scan without changing the core theme.

4. Preserve a professional government-office tone.
   Typography, spacing, and color usage should look formal and dependable. Avoid flashy saturation, playful iconography, or trend-heavy effects.

5. Design for data readability first.
   Tables, badges, timestamps, and pesos amounts should be easy to compare at a glance. Visual styling must support decision-making, not distract from it.

## Recommended Page Structure

1. Hero summary band
   Use a premium but restrained header block with a short page description and 3 concise KPI cards.

2. Operational snapshot row
   Show visible item count, pipeline count, and visible request value in compact cards.

3. Filter and status control panel
   Group search, reset, and filter chips inside one elevated panel to reduce fragmentation.

4. Data table shell
   Use a bordered, rounded container with a soft header background, comfortable row spacing, and clear hover feedback.

## Visual Standards

### Color

- Primary: `#1a3a2d`
- Secondary green: `#2d5a4a`
- Accent gold: `#f9bf0f`
- Surface background: white and near-white green tints only
- Success, warning, danger, and info states may use semantic colors, but the page chrome should stay green-led

### Typography

- Headings should be bold and compact with clear spacing separation from body text
- Supporting text should remain muted gray for readability
- Labels and metadata should use uppercase tracking sparingly for structure, not everywhere

### Spacing

- Main sections: 24px to 32px separation
- Card interiors: 16px to 24px padding
- Dense table content should still maintain breathable vertical rhythm

### Radius and Elevation

- Major panels: 24px to 28px radius
- Inputs and buttons: 12px to 18px radius
- Shadows should be soft and wide, not dark and sharp

## Table Standards

- Keep header background subtly tinted for distinction
- Use one primary text line and one secondary metadata line where needed
- Monetary values should be emphasized with heavier weight or a soft chip treatment
- Action buttons should look deliberate and aligned with the green theme
- Hover states should improve focus without causing layout movement beyond slight lift

## Filter Standards

- Search field should be prominent and wide enough for practical use
- Reset should look secondary but still visible
- Status chips should use semantic coloring with consistent padding and count badges
- Result count and helper text should sit close to the controls, not detached from them

## Modal Standards

- Maintain the same green branded header and white content surface
- Ensure open state always starts at the top of the form
- Keep primary action buttons fixed in a visible footer area when forms are long
- Focus behavior must not force scroll position away from the header

## Responsiveness

- On mobile, stack summary cards vertically and keep search full-width
- On tablet, preserve clear grouping before trying to compress everything into one row
- On desktop, align controls horizontally only when spacing remains comfortable

## Implementation Notes For This Codebase

- Prefer Tailwind utility classes in Blade templates for page-specific treatments
- Keep JavaScript behavior centralized in the existing purchase request scripts
- Rebuild with `npm run build` after view or asset changes
- Avoid introducing a new color system unless the whole application is being standardized