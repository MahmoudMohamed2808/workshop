# Appliance Repair Workshop Management System
### Final Feature List — Phase 1 (MVP)

**Stack:** Laravel 13 · React Starter Kit (Inertia + Fortify) · Filament v5 (Admin Panel) · spatie/laravel-medialibrary · spatie/laravel-tags

---

## 1. Panel Split

| Panel | Built With | Used By |
|---|---|---|
| **Admin Panel** | Filament v5 | Admin, Staff, Technician, Accountant |
| **Customer-facing app (optional, later)** | React + Inertia | Customers (approve repair, view invoice) |

> Note: since Filament v5 handles CRUD + relations + actions natively, most of Phase 1 can be built entirely inside Filament resources. The React/Inertia starter kit is mainly needed if you want a customer portal or public tracking page later.

---

## 2. Master Data (Dynamic Setup)

- [ ] **Device Types** (Refrigerator, Washing Machine, Blender, Motor, AC...) — CRUD from Filament, no code changes needed
- [ ] **Brands** — linked to Device Type (Filament relation manager)
- [ ] **Models** — linked to Brand, optional field on Repair Order
- [ ] **Problems / Complaints**
  - Linked to Device Type (required)
  - Optionally scoped to a specific Brand or Model
  - Reusable across multiple brands
  - Default estimated cost (min–max range or fixed)
  - Active/Inactive toggle
  - Tracks usage count (to surface "most common problems" first)
- [ ] Use **spatie/laravel-tags** to tag Problems (e.g. `electrical`, `mechanical`, `cooling`) for quick filtering in Filament and reporting

---

## 3. Customers

- [ ] Name, Phone, WhatsApp number (if different), Address, Notes
- [ ] Customer History (relation manager showing all devices, repair orders, invoices, payments, warranties)
- [ ] Filament global search on customer name/phone

---

## 4. Device Intake / Repair Order

- [ ] Auto-generated order number: `RO-2026-000125`
- [ ] Select Customer → Device Type → Brand → Model (optional) → Serial Number (optional)
- [ ] Device condition on intake (dropdown/text)
- [ ] Accessories received (repeater field)
- [ ] Customer complaint (free text) **+** selection from existing Problems list (multi-select)
- [ ] **Photos on intake — required (at least 1)**, via spatie/laravel-medialibrary
  - Suggested media collections: `intake_photos`, `repair_photos`, `delivery_photos`
- [ ] Auto warranty check: if Serial Number or Customer+Device matches a previous order still under warranty → show alert banner in Filament

---

## 5. Inspection & Diagnosis

- [ ] Separate field: **Technician Diagnosis** (distinct from customer complaint)
- [ ] Support **multiple problems per order**, each with its own cost — auto-summed total
- [ ] Required spare parts selection (deducts from inventory automatically via model events/observers)
- [ ] Labor cost field
- [ ] Additional cost field (free-form line items)
- [ ] Estimated completion date
- [ ] Technician notes (rich text or plain textarea)
- [ ] "Beyond Repair" status option with reason field

---

## 6. Customer Approval

- [ ] Status: `Waiting Approval`
- [ ] Approve / Reject actions (Filament action buttons, or customer-facing link if using Inertia portal)
- [ ] Rejection reason field
- [ ] Timestamp + user who recorded the decision

---

## 7. Repair Order Status Workflow

Implement as a Filament-managed enum/state field with a visual workflow (e.g. using a status badge + Filament's `Actions` to transition):

```
Received → Inspection → Waiting Approval → Approved →
In Repair (includes internal "Tested" checkbox) →
Ready for Delivery → Delivered
```

Exception states: `Rejected` · `Cancelled` · `Beyond Repair`

- [ ] Status change restricted by role (e.g. only Technician can move to "In Repair")
- [ ] **Activity Log** on every status/cost change — who changed what, when (use `spatie/laravel-activitylog` — recommend adding this package too, pairs naturally with your stack)

---

## 8. Spare Parts & Inventory (Simple)

- [ ] Part: Name, Code, Purchase Price, Selling Price, Quantity, Minimum Stock Alert
- [ ] Auto stock deduction when a part is attached to a Repair Order
- [ ] Low-stock indicator/notification in Filament dashboard widget
- [ ] (Suppliers & Purchase Orders deferred to Phase 2)

---

## 9. Invoicing

- [ ] Auto-generated invoice number: `INV-2026-000458`
- [ ] Line items: Parts + Labor + Additional Cost − Discount = Total
- [ ] Paid / Remaining auto-calculated
- [ ] Generate PDF (use `spatie/laravel-pdf` or `barryvdh/laravel-dompdf` — either integrates cleanly with Filament actions)
- [ ] Attach generated PDF to the order via medialibrary (`invoices` collection)

---

## 10. Payments

- [ ] Methods: Cash, Bank Transfer, Wallet, Card
- [ ] Support partial/multiple payments per order
- [ ] Running total: Total → Paid → Remaining
- [ ] Payment history log per order

---

## 11. WhatsApp Integration

- [ ] Integrate via a WhatsApp Business API provider (e.g. Twilio, Meta Cloud API, or a local aggregator) — this needs its own service class, not a Spatie package
- [ ] Auto-send triggers:
  - Device received
  - Diagnosis complete / approval request
  - Device ready for pickup
  - Invoice issued (send PDF)
  - Payment reminder (outstanding balance)
- [ ] Message log table: recipient, message type, status (sent/failed), timestamp
- [ ] Retry action in Filament for failed messages

---

## 12. Warranty

- [ ] Warranty duration field per completed repair (days)
- [ ] Auto-calculated Warranty Start / End dates
- [ ] Alert banner when creating a new order for a device/customer still under active warranty

---

## 13. Roles & Permissions

- [ ] Use `spatie/laravel-permission` (pairs with your existing Spatie packages) + Filament Shield plugin for role management UI
- [ ] Roles for Phase 1:
  - **Admin** — full access
  - **Staff** — customers, intake, invoices, payments
  - **Technician** — diagnosis, repair status updates, parts usage

---

## 14. Dashboard (Filament Widgets)

- [ ] Today's counters: Received / In Repair / Ready / Delivered
- [ ] Today's revenue, paid, remaining
- [ ] Low-stock parts widget
- [ ] Orders awaiting customer approval widget

---

## 15. Smart / UX Enhancements (Recommended Additions)

- [ ] **Most-used problems surfaced first** in the selection list (order by usage_count)
- [ ] **"Other problem" free-text fallback** — staff review later and formalize into the Problems list if it recurs
- [ ] **Last 3 recorded costs for the same problem** shown as a reference hint when setting price
- [ ] **"Copy from previous device" action** — if same customer + serial number exists, prefill Brand/Model instantly
- [ ] **QR code per Repair Order** (generate with `simplesoftwareio/simple-qrcode`) — printable sticker for physical device tracking
- [ ] **Digital confirmation on intake/delivery** — simple signature pad or typed name confirmation, stored with timestamp

---

## Phase 2 (Deferred — after MVP is running and validated)

- [ ] Suppliers & Purchase Orders
- [ ] Detailed expense categories (rent, electricity, salaries, etc.)
- [ ] Technician commission calculation
- [ ] Finer-grained permissions (separate Accountant role)
- [ ] Priority flag on repair orders (urgent/normal)
- [ ] Warranty expiry reminder notifications (before it lapses, not just on lookup)

## Phase 3 (Reporting & Analytics)

- [ ] Most common problems / brands / models
- [ ] Average repair time per technician
- [ ] Repeat-complaint rate per device/customer (quality signal)
- [ ] Net profit reports (revenue − parts cost − expenses)
- [ ] Exportable reports (Excel/PDF) — Filament has built-in export actions for this

---

## Suggested Additional Packages (beyond what you listed)

| Package | Purpose |
|---|---|
| `spatie/laravel-activitylog` | Audit trail for status/cost changes |
| `spatie/laravel-permission` + Filament Shield | Roles & permissions with Filament UI |
| `spatie/laravel-pdf` or `barryvdh/laravel-dompdf` | Invoice PDF generation |
| `simplesoftwareio/simple-qrcode` | QR code per repair order |
| `spatie/laravel-model-states` (optional) | Cleaner state machine for the Repair Order workflow instead of a plain enum |
