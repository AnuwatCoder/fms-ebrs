# Equipment Borrowing & Return System — Project Analysis

วันที่วิเคราะห์: 2026-09-17

## 1. Existing Project Analysis

- ก่อนเริ่มงานเป็น Laravel skeleton ที่แทบยังไม่มี application code: route `/` แสดง `welcome.blade.php`, มีเพียง `User` model และ default migrations สำหรับ users/cache/jobs
- เดิมใช้ Laravel 12.69.2, PHP constraint `^8.2` และ CLI ใน PATH เป็น PHP 8.2.12 ซึ่งไม่ตรง requirement
- ไม่มี Blade layout, Livewire component, authentication controller, OIDC, policy, role/permission หรือ domain model เดิมให้ reuse
- Testing ใช้ Pest; database ใน test เป็น SQLite memory database
- `.env.example` เดิมเป็น SQLite ขณะที่ production requirement ระบุ PostgreSQL
- `public/template` เป็นไฟล์ที่ยังไม่ถูก track ใน Git และไม่ได้ถูกแก้ไขในการทำ Foundation
- Phase 2 อัปเกรดเป็น Laravel 13.32.0 / PHP `^8.4`, Livewire 4.4.5, Spatie Permission 8.3.0 และ Pest 4.7.8; authentication เปลี่ยนมาใช้ SocialiteProviders Authentik 5.3.0
- Phase 3 เชื่อม Liner Admin เข้ากับ Blade app/auth layouts, permission-aware navigation, OIDC login page และ dashboard ที่อ่านข้อมูลจริงจากฐานข้อมูลแล้ว

## 2. Template Analysis

Template คือ **Liner Admin** มี 154 ไฟล์ ขนาดรวมประมาณ 7.54 MB:

- HTML 61 ไฟล์
- JPG 90 ไฟล์
- CSS 1 ไฟล์: `assets/css/theme.css` (ประมาณ 298 KB)
- JavaScript 1 ไฟล์: `assets/js/app.js` (ประมาณ 182 KB)
- SVG logo 1 ไฟล์

โครงสร้างหลักใน `dashboard.html` คือ sidebar, header, backdrop/settings panel และ `<main class="app-main">` โดย layout ถูกทำซ้ำในแต่ละ HTML และยังไม่มี partial/template engine เดิม

สิ่งที่มีและ reuse ได้:

- Dashboard, cards, tables, responsive tables, search/filter/pagination appearance
- Forms, horizontal forms, validation states
- Alerts, badges/chips, dropdowns, dialogs, accordion, tabs, tooltip, popover
- Login/register/forgot-password/two-step/error/maintenance/profile/account pages
- Responsive sidebar/header, dark mode, color presets และ RTL behavior
- Lucide icons และ custom icons
- ApexCharts examples ใน dashboard/analytics/chart pages

ข้อสังเกตสำคัญ:

- HTML 59 หน้าอ้าง Bootstrap 5.3.3 CSS และ Lucide จาก CDN; 55 หน้าอ้าง Bootstrap bundle จาก CDN; 12 หน้าอ้าง ApexCharts 3.49.1 จาก CDN
- เดิม `public/template` ไม่มีไฟล์ local ของ Bootstrap, Lucide หรือ ApexCharts; Phase 3 จึง pin และติดตั้ง Bootstrap 5.3.3, Lucide 0.468.0 และ ApexCharts 3.49.1 ไว้ใต้ `public/template/vendor` ส่วนฟอนต์ใช้ Anuphan ที่มีอยู่ใน `public/fonts` โดยหน้า Laravel ไม่เรียก CDN
- Template ไม่มี `<footer>` ที่เป็น shared page footer ชัดเจน มีเพียง sidebar/settings/content footer patterns
- ไม่มี jQuery, DataTables, Select2 หรือ date picker ที่ตรวจพบ จึงไม่ควรสมมติหรือเพิ่ม package เหล่านี้
- class หลายตัวมีลักษณะ utility class แต่ถูกนิยามใน `theme.css`; UI จริงพึ่ง Bootstrap 5.3.3 + theme CSS ไม่ใช่ Tailwind runtime

## 3. Existing Components That Can Be Reused

- Laravel default application bootstrap, cache/jobs/session migrations, Vite setup และ Pest test harness
- Template: sidebar/header/settings panel จาก `dashboard.html`, auth shell จาก `login.html`, table patterns จาก `basic-table.html`/`enhanced-table.html`, forms จาก `form-*.html`, confirmation จาก `dialog.html`, alert จาก `alert.html`, error/profile pages
- ไม่พบ service/helper/trait/layout/domain component เดิมที่ซ้ำกับระบบใหม่

## 4. Proposed Architecture

ใช้ modular monolith ตาม feature โดยไม่เพิ่ม modular package:

```text
app/
├── Actions/
│   ├── Authentication/
│   ├── Borrowing/
│   ├── Approval/
│   ├── Checkout/
│   └── Returns/
├── Enums/
├── Http/Controllers/Auth/
├── Livewire/{Dashboard,Equipment,Borrow,Approval,Checkout,ReturnEquipment,Reports,Administration}/
├── Models/
├── Policies/
└── Services/
```

Livewire รับผิดชอบ interaction และ validation ระดับ input; business operation ที่เปลี่ยนหลาย record อยู่ใน Action/Service และครอบด้วย transaction/row lock; authorization อยู่ใน Policy/Gate/permission middleware

## 5. Database Schema

Foundation สร้าง schema ต่อไปนี้:

- Identity/RBAC: `users`, `user_identities`, `roles`, `permissions`, Spatie pivot tables
- Master data: `equipment_categories`, `equipment`
- Borrowing: `borrow_requests`, `borrow_request_items`, `borrow_approvals`
- Operations: `equipment_checkouts`, `equipment_returns`, `equipment_incidents`
- Infrastructure: `request_sequences`, `audit_logs`

หลักการที่ใช้:

- QR/public URL ใช้ `equipment.public_id` แบบ ULID; database PK ยังคงเป็น bigint
- unique constraints สำหรับ equipment code, asset number, serial number, request number, identity `(provider, subject)` และ request item `(borrow_request_id, equipment_id)`
- indexes สำหรับ status, category, user, borrow/return dates และ overdue queries
- master data ใช้ soft delete; transaction history ไม่มี soft delete และ FK ใช้ restrict เพื่อกันการลบประวัติ
- `request_sequences` เตรียมไว้สำหรับสร้าง `BR-YYYYMM-XXXXX` ภายใน transaction/row lock โดยไม่ใช้ `MAX(id)+1`
- `audit_logs` เป็น append-only foundation; wiring สำหรับ event สำคัญจะทำพร้อมแต่ละ business action

## 6. Models & Relationships

- `User hasMany UserIdentity, BorrowRequest`; ใช้ `HasRoles`
- `EquipmentCategory hasMany Equipment`
- `Equipment belongsTo EquipmentCategory`; hasMany borrow items/incidents
- `BorrowRequest belongsTo User`; hasMany items/approvals/incidents
- `BorrowRequestItem belongsTo BorrowRequest, Equipment`; hasOne checkout/return
- `BorrowApproval belongsTo BorrowRequest, approver(User)`
- `EquipmentCheckout belongsTo item, staff(User)`
- `EquipmentReturn belongsTo item, receiver(User)`
- `EquipmentIncident belongsTo equipment, optional borrow request, reporter(User)`
- `AuditLog belongsTo causer(User)` และ morphTo subject

Enum ที่สร้าง: `EquipmentStatus`, `BorrowRequestStatus`, `BorrowItemStatus`, `ApprovalAction`, `ReturnStatus`, `IncidentType`

## 7. Roles & Permissions

| Role | สิทธิ์หลัก |
|---|---|
| SuperAdmin | Gate bypass และ permissions ทั้งหมด |
| Admin | equipment/category/operations/maintenance/reports/audit |
| Approver | ดูคำขอและ approve/reject |
| Staff | ดูอุปกรณ์/คำขอ, checkout, return, maintenance |
| Borrower | ดูอุปกรณ์, สร้าง/ดูของตนเอง/ยกเลิกคำขอ |

Permissions แยก granular ตั้งแต่ `equipment.*`, `category.*`, `borrow.*`, `approval.*`, `checkout.*`, `return.*`, `maintenance.*`, reports, administration และ audit โดย seeder ทำงานแบบ idempotent

## 8. Routes

Foundation ที่มีแล้ว:

```text
GET  /login                 login
GET  /auth/authentik/callback    auth.authentik.callback
POST /logout                logout
```

แผน route หลังมี UI/components:

```text
/dashboard
/equipment, /equipment/categories, /equipment/scan/{equipment:public_id}
/borrow/requests, /borrow/requests/create, /borrow/requests/{borrowRequest}
/approval/pending, /approval/{borrowRequest}
/checkout, /checkout/{borrowRequest}
/return, /return/{borrowRequestItem}
/maintenance/incidents
/reports/{equipment,borrowing,overdue,damage}
/admin/{users,roles,permissions,settings}
```

ทุก feature route จะอยู่ใต้ `auth` และ permission middleware; policy ตรวจ ownership/resource ซ้ำใน server-side action

## 9. Livewire Components

แผน component:

- Dashboard: `Overview`, `BorrowerOverview`
- Equipment: `EquipmentIndex`, `EquipmentForm`, `CategoryIndex`, `EquipmentDetails`, `QrScanner`
- Borrow: `RequestIndex`, `RequestForm`, `RequestDetails`
- Approval: `PendingRequests`, `ReviewRequest`
- Checkout: `CheckoutQueue`, `ProcessCheckout`
- ReturnEquipment: `ReturnQueue`, `ProcessReturn`
- Reports: `EquipmentReport`, `BorrowingReport`, `OverdueReport`, `DamageReport`
- Administration: `UserIndex`, `RoleIndex`, `PermissionIndex`, `SettingsForm`

ใช้ server-side query/filter/sort/pagination และแยก critical operation ไป Action classes

## 10. Borrow/Return Workflow

`BorrowRequestStatus` กำหนด transition แบบ explicit:

```text
DRAFT -> PENDING | CANCELLED
PENDING -> APPROVED | REJECTED | CANCELLED
APPROVED -> READY_FOR_PICKUP | CANCELLED
READY_FOR_PICKUP -> BORROWED | CANCELLED
BORROWED -> RETURNED | OVERDUE
OVERDUE -> RETURNED
```

ก่อน approve/checkout จะ query availability ตามช่วงวันที่ และ lock equipment + conflicting active items ด้วย PostgreSQL `lockForUpdate()` ใน transaction. Return outcome map สถานะอุปกรณ์เป็น AVAILABLE/DAMAGED/LOST/MAINTENANCE ผ่าน enum โดยตรง

## 11. Security Considerations

- Authentik login ใช้ OAuth 2.0 Authorization Code flow ผ่าน Socialite, มี session state protection และดึง standard OIDC claims จาก Authentik userinfo endpoint ผ่าน HTTPS
- local account ผูกด้วย `(provider, sub)` เท่านั้น ไม่ auto-link ด้วย email เพื่อลด account takeover; email ซ้ำต้องให้ผู้ดูแล link
- session regenerate หลัง login และ invalidate/regenerate CSRF token หลัง logout
- login/callback rate limit 10 requests/minute/IP
- password nullable และไม่มี password login route; development admin ไม่มี default password
- SuperAdmin bypass ผ่าน server-side Gate; route/component ยังต้องใช้ permission + policy
- Laravel 13 session ใช้ JSON serialization และ cache ไม่อนุญาต unserialize arbitrary classes
- รูปภาพต้องใช้ Storage API, generated filename, image MIME/extension/size validation ใน Phase 4
- availability/checkout/return ต้องใช้ transaction + row locks และไม่เชื่อ status/user/role จาก browser

## 12. Implementation Plan

1. Phase 1 — analysis (เสร็จ)
2. Phase 2 — Laravel 13 upgrade, database, enums, models, RBAC, OIDC foundation (เสร็จในขอบเขต foundation)
3. Phase 3 — vendor template dependencies locally; แยก Blade app/auth layouts และ partials (เสร็จ)
4. Phase 4 — categories/equipment CRUD, secure image, QR
5. Phase 5 — borrow request, request number service, availability and overlap locking
6. Phase 6 — approval actions/history
7. Phase 7 — checkout/return/condition/incidents/QR workflow
8. Phase 8 — dashboards/reports/filter/export CSV
9. Phase 9 — policies, validation, race/IDOR/upload tests, audit wiring
10. Phase 10 — Docker/PostgreSQL/Redis, scheduler, deployment, README and production hardening

## 13. Files To Create

- `app/Enums/*`, domain models, OIDC sync action/controller
- domain migrations and Spatie migration/config
- role/category/development-admin seeders
- Phase 3 สร้าง app/auth Blade layouts, shared partials, OIDC login, database-backed dashboard/controller และ UI integration tests แล้ว
- Phase 4 onward: Livewire components, policies, feature actions/services, factories/tests, scheduler command, Docker/deployment files

## 14. Files To Modify

- `composer.json` / `composer.lock`
- `.env.example`
- `app/Models/User.php`, `app/Providers/AppServiceProvider.php`
- `bootstrap/app.php`, `config/app.php`, `config/cache.php`, `config/session.php`
- `database/factories/UserFactory.php`, `database/seeders/DatabaseSeeder.php`
- `routes/web.php`
- Phase 3 แก้ routes/auth redirect/enum display metadata และเพิ่ม package/local vendor assets โดยคงไฟล์ต้นฉบับของ template ไว้
- Phase 4 onward: README, routes, layouts และ relevant config ตาม feature

## 15. Risks / Questions

- ต้องทราบ OIDC issuer จริงและนโยบาย claims (email บังคับหรือไม่, logout endpoint, group-to-role mapping)
- หน้า HTML ต้นฉบับของ template ยังอ้าง CDN แต่ route ของ Laravel ใช้ local vendor assets เท่านั้น; ห้าม copy CDN reference กลับมาใน Blade/Livewire pages ใหม่
- ต้องกำหนด timezone/วันหยุดและนิยาม “overdue” ว่านับตาม date หรือเวลาทำการ; foundation ใช้ `Asia/Bangkok` ผ่าน env
- ต้องยืนยันว่า asset number และ serial number ต้อง unique เสมอหรือ unique เฉพาะค่าที่ไม่เป็น null; schema ปัจจุบันใช้ unique nullable ซึ่งเหมาะกับ PostgreSQL
- ต้องตัดสินใจว่า Admin สามารถจัดการ users/roles ได้หรือสงวนให้ SuperAdmin; matrix ปัจจุบันสงวนไว้ให้ SuperAdmin
- PostgreSQL production ต้องทดสอบ concurrency แยกจาก SQLite test suite โดยเฉพาะ row locking และ request sequence
