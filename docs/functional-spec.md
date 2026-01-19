# Helpdesk Application — Functional Specification

Version: 1.0  
Date: 2025-12-03

## 1. Purpose and Scope
The Helpdesk application manages internal support tickets across branches (Cabang), IT handlers, and optionally Vendors. This document defines functional requirements, roles, workflows, data structures, validations, and constraints based on the current codebase and database migrations.

## 2. Roles and Permissions
- IT: Full ticket management, assignment, progress updates, close/reopen, export, user management, view statistics.
- CABANG: Create tickets, view own tickets, comment, download attachments.
- VENDOR: View assigned tickets, add vendor follow-up notes.
- ADMIN: Appears in UI but not present in `users.role` enum. Considered reserved; not storable with current DB schema.

Role source of truth: users.role enum (IT, CABANG, VENDOR). Default: CABANG.

## 3. Ticket Lifecycle
- Status values: OPEN → ON_PROGRESS → (optional) ESKALASI_VENDOR → (optional) VENDOR_RESOLVED → CLOSED
- Key timestamps: taken_at (when IT takes), progress_at, vendor_followup_at, closed_at
- Additional flags/fields: eskalasi (TIDAK/VENDOR), progress_note, vendor_followup, closed_note, root_cause

Rules:
- Only IT can take/release/reopen/close and set eskalasi/vendor follow-up via IT routes.
- When CLOSED: add comment/attachment is disabled; delete comment hidden and blocked in controller.
- Comment deletion: only the comment owner; blocked if ticket is CLOSED.

## 4. Functional Areas
### 4.1 Authentication and Navigation
- Home redirects to `login`.
- `Dashboard` entry-point redirects by role: CABANG → create ticket form; IT → ticket list (with filters); VENDOR → assigned tickets.
- Global notifications dropdown with unread highlighting and responsive mobile layout.

### 4.2 Profile Management (All Users)
- Edit profile information: name, email.
- Update password: current, new, confirmation.
- Delete account section removed (by requirement).
- Forms use consistent white inputs, app-style buttons, and responsive cards.

### 4.3 Ticket Management — CABANG
- Create new ticket: kategori/category/subcategory, deskripsi, optional lampiran.
- View list of own tickets and details.
- Comment on a ticket (unless CLOSED). Upload comment attachment.

### 4.4 Ticket Management — IT
- View all tickets with filters (search/nomor/deskripsi/kategori, date ranges, status). CSV/PNG export.
- Take/release ticket, set progress note, set eskalasi to vendor, assign vendor, vendor follow-up, close/reopen.
- View statistics page.

### 4.5 Ticket Management — VENDOR
- View assigned tickets; add vendor follow-up.

### 4.6 User Management — IT
- CRUD users with fields: username, name, email, role, password (create), password optional (edit).
- Filters: search by name/email/username and filter by role.
- Mobile: action buttons stack vertically; on wider screens they align horizontally.
- Prevent deleting self.

## 5. UI/UX Requirements (selected)
- Filters and buttons centered on IT dashboard; consistent heights; mobile stacking order: search → date from → date to → status → actions.
- Date inputs show placeholders (Tgl Awal/Tgl Akhir) while preserving native date pickers via focus/blur type toggle.
- Status badge sizes adapt on mobile to prevent overflow.
- Notifications: unread highlighted yellow; mobile dropdown full-width, fixed position, word wrapping enabled.

## 6. Validation Rules
- User create: username (unique, 3–50), name (>=3), email (unique, email), password (>=8), role ∈ {IT, CABANG, VENDOR} (ADMIN in UI but not storable with current enum).
- User update: username unique except self; email unique except self; role as above; password optional (>=8 when present).
- Tickets: nomor_tiket unique; kategori string (nullable, len 255); deskripsi required; lampiran optional string path.
- Comments: body required; attachment optional string path.

## 7. Notifications
- Stored in `notifications` table (UUID PK). Per-user, supports mark one/mark all read.

## 8. Data Model (current DB)
### 8.1 users
- id (PK), name, username (unique), email (unique), email_verified_at, password, remember_token, role enum {IT,CABANG,VENDOR} default CABANG, timestamps.

### 8.2 tickets
- id (PK), category_id (FK->categories, set null), subcategory_id (FK->subcategories, set null), nomor_tiket unique, user_id (FK->users), it_id (FK->users, null on delete), vendor_id (FK->users, null on delete), kategori string(255) nullable, deskripsi text, lampiran string nullable, status enum {OPEN, ON_PROGRESS, ESKALASI_VENDOR, VENDOR_RESOLVED, CLOSED} default OPEN, eskalasi enum {TIDAK, VENDOR} (present), progress_note text nullable, progress_at ts nullable, taken_at ts nullable, closed_at ts nullable, vendor_followup text nullable, vendor_followup_at ts nullable, closed_note text nullable, root_cause string(100) nullable, timestamps.

### 8.3 ticket_comments
- id (PK), ticket_id (FK cascade), user_id (FK cascade), body text, attachment string nullable, timestamps.

### 8.4 categories
- id (PK), name, slug unique nullable, description nullable, timestamps.

### 8.5 subcategories
- id (PK), category_id (FK cascade), name, slug unique nullable, description nullable, timestamps.

### 8.6 ticket_histories
- id (PK), ticket_id (FK cascade), user_id (FK nullOnDelete), action string(50), note text nullable, meta json nullable, timestamps.

### 8.7 notifications
- id (UUID PK), type, notifiable_type, notifiable_id, data text, read_at nullable, timestamps.

## 9. Route Map (main web routes)
- GET `/` → redirect to login.
- Authenticated group:
  - Profile: GET `/profile` (edit), PATCH `/profile` (update), DELETE `/profile` (disabled in UI).
  - Notifications: GET `/notifications`, POST `/notifications/read-all`, POST `/notifications/{id}/read`.
  - Dashboard redirector: GET `/dashboard` → role-based redirect.
  - Shared tickets: GET `/ticket/{ticket}` (show), GET `/ticket/{ticket}/download`, POST `/ticket/{ticket}/comment`, DELETE `/comment/{comment}`, GET `/ticket/comment/{comment}/download`.
  - CABANG: GET `/cabang/dashboard`, POST `/cabang/ticket`, GET `/cabang/tickets`.
  - IT: GET `/it/dashboard`, GET `/it/tickets/export`, GET `/it/my-tickets`, GET `/it/stats`, POST `/it/ticket/{ticket}/take|release|reopen|close|eskalasi|vendor-followup|assign-vendor|progress`.
  - VENDOR: GET `/vendor/dashboard`, GET `/vendor/tickets`, POST `/vendor/ticket/{ticket}/followup`.
  - IT User Management: `it/users` index/create/store/edit/update/destroy.
- Public helper: GET `/categories/{id}/subcategories` for dependent dropdowns.

## 10. Exports
- IT dashboard supports CSV export (and PNG via html2canvas in UI for snapshots). Filters apply to exported data.

## 11. Security and Constraints
- All application areas behind `auth` middleware.
- Role-based route groups for CABANG/IT/VENDOR; additional inline guard for IT User Management controller.
- Owner-only comment deletion and disabled actions on CLOSED enforced in both view and controller.

## 12. Non-Functional
- Responsive UI with mobile-first adjustments.
- Standardized component heights and spacing.
- Word wrapping for long notification content to avoid clipping.

## 13. Known Gaps / Notes
- `ADMIN` role is present in forms but not in DB enum. Storing ADMIN will fail with current schema; either remove option or alter enum to include ADMIN.
- `eskalasi` is added by multiple migrations; effective state includes it. Ensure column order conflicts are handled when running fresh migrations.

## 14. Acceptance Criteria (samples)
- Profile page shows two cards (Informasi Akun, Ubah Password) with white inputs and saves successfully.
- IT User list can be filtered by query (name/email/username) and by role. Buttons are stacked on mobile and inline on desktop.
- CABANG cannot add comments when ticket status = CLOSED; delete buttons are hidden; server rejects forced deletes.
- IT can take, release, close, reopen, add progress, escalate to vendor, and see timestamps updated accordingly.

---
This spec mirrors the current implementation and migrations in the repository at the time of writing.
