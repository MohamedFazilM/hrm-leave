# HRM Leave & Attendance Management System

An enterprise-ready Human Resource Management (HRM) web application built with **Laravel 12**, **Blade**, **Bootstrap 5**, and **Laravel Sanctum**. This application streamlines employee profile management, department structures, leave requests with strict date overlap and balance enforcement, daily check-in/check-out attendance tracking, monthly attendance reporting with streaming CSV exports, and a full RESTful API for mobile or third-party integrations.

---

## 🚀 Key Features

### 🔐 1. Authentication & Role-Based Access
- Hand-crafted session authentication (Login, Register, Logout).
- 3 Distinct System Roles: `admin`, `manager`, and `employee`.
- Middleware-enforced authorization policies preventing unauthorized access to administrative features.

### 🏢 2. Departments & Employee Management (Admin Only)
- **Departments CRUD:** Manage organizational units with unique department codes and descriptions.
- **Employee Directory CRUD:** Comprehensive staff profiles (Name, Email, Role, Department, Designation, Join Date, Phone).
- Dynamic search by employee name/email and department filtering with pagination.

### 📅 3. Leave Management System
- Seeded leave types (**Casual Leave**, **Sick Leave**, **Earned Leave**) with custom yearly day allowances.
- **Leave Application:** Validates future date ranges (`from_date >= today`, `to_date >= from_date`).
- **Overlap Prevention:** Prevents employees from applying for duplicate or overlapping date ranges.
- **Balance Validation:** Auto-calculates requested days and blocks requests exceeding remaining yearly allowances.
- **Manager/Admin Approval Flow:** Approvals update available leave balances; mandatory comment required when rejecting.
- **Employee Cancellation:** Employees can cancel their own `pending` leave requests.

### ⏱️ 4. Attendance & Reports
- **Daily Check-In & Check-Out:** Single check-in/out per employee per day.
- **Late Cutoff Tracking:** Configurable cutoff time (default `09:30 AM`). Arrival after cutoff is flagged as `late`.
- **Employee History:** Personal paginated attendance log showing check-in/out times and working hours duration.
- **Admin/Manager Logs:** Master attendance view with date pickers and employee/department filters.
- **Monthly Attendance Summary Report:** Per-employee metrics calculating Present Days, Late Days, Approved Leave Days, and Absent Days (`Working Days - (Present + Late + Leave)`).
- **Native CSV Export:** Memory-efficient CSV file streaming without external packages.

### 🔌 5. RESTful API (`/api/v1`)
- Token-based authentication using **Laravel Sanctum**.
- Form Requests and API Resources (`UserResource`, `LeaveRequestResource`, `AttendanceResource`) returning standardized JSON data.
- Full API coverage: Auth login/logout/me, Leave balances, Leave application/cancellation/approval, Daily check-in/check-out, and Attendance history.
- Detailed documentation in [`docs/api.md`](docs/api.md).

---

## 🛠️ Tech Stack

- **Framework:** Laravel 12
- **Language:** PHP 8.2+
- **Database:** MySQL (XAMPP / MariaDB)
- **API Authentication:** Laravel Sanctum
- **Frontend UI:** Blade Templates + Bootstrap 5 CDN + Bootstrap Icons
- **Testing:** PHPUnit with SQLite In-Memory Database

---

## ⚙️ Setup & Installation (Windows / XAMPP)

### Prerequisites & Required PHP Extensions
Ensure PHP 8.2+ is installed with the following extensions enabled in `php.ini`:
- `pdo_mysql`
- `openssl`
- `mbstring`
- `tokenizer`
- `xml`
- `ctype`
- `bcmath`

### Step-by-Step Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/MohamedFazilM/hrm-leave.git
   cd hrm-leave
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Configure Environment File:**
   Copy `.env.example` to `.env`:
   ```bash
   cp .env.example .env
   ```
   Ensure your MySQL database configuration in `.env` matches your XAMPP settings:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=hrm_leave
   DB_USERNAME=root
   DB_PASSWORD=
   ```

4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations & Seeders:**
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start Local Server:**
   ```bash
   php artisan serve
   ```
   Open your browser at `http://127.0.0.1:8000`.

---

## 🔑 Demo Credentials (Seeded Accounts)

All seeded test accounts use the password: **`password`**

| Role | Email Address | Password | Department |
|---|---|---|---|
| **Admin** | `admin@hrm.test` | `password` | Human Resources |
| **Manager** | `manager@hrm.test` | `password` | Engineering & Development |
| **Employee** | `employee1@hrm.test` | `password` | Engineering & Development |
| **Employee** | `employee2@hrm.test` | `password` | Marketing & Sales |

---

## 📐 Key Business Logic

1. **Date Overlap Check:**
   Prevents overlapping requests using the SQL condition:
   $$\text{Existing From Date} \le \text{Requested To Date} \quad \text{AND} \quad \text{Existing To Date} \ge \text{Requested From Date}$$
2. **Leave Balance Calculation:**
   $$\text{Remaining Balance} = \text{Max Yearly Allowance} - \text{Total Approved Leave Days in Current Year}$$
3. **Late Attendance Cutoff:**
   Configured in `config/hrm.php` (`'late_cutoff' => '09:30'`). Any check-in past `09:30 AM` is automatically assigned status `'late'`.
4. **Absent Days Formula:**
   $$\text{Absent Days} = \text{Total Business Working Days (Mon-Fri)} - (\text{Present Days} + \text{Late Days} + \text{Approved Leave Days})$$

---

## 🔌 API Overview & cURL Examples

For complete API specifications, payload schemas, and response samples, refer to [`docs/api.md`](docs/api.md).

### 1. Authenticate & Obtain Bearer Token
```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"employee1@hrm.test","password":"password"}'
```

### 2. Fetch User Profile (Authenticated)
```bash
curl -X GET http://127.0.0.1:8000/api/v1/me \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_SANCTUM_TOKEN"
```

---

## 🧪 Running Automated Tests

Run the full PHPUnit feature test suite covering Leave rules, Authorization middleware, Attendance logic, Monthly reports, and REST API endpoints:

```bash
php artisan test
```

---

## 📁 Folder Structure Overview

```
hrm-leave/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/V1/         # Sanctum API Controllers
│   │   │   └── ...             # Web Controllers (Auth, Leave, Attendance, Reports)
│   │   ├── Middleware/          # Role Authorization Middleware
│   │   ├── Requests/            # Form Validation Requests
│   │   └── Resources/           # API JSON Resources
│   └── Models/                  # Eloquent Models (User, Department, LeaveType, LeaveRequest, Attendance)
├── config/
│   └── hrm.php                  # System configuration (Late cutoff time)
├── database/
│   ├── factories/               # Database Factories for PHPUnit testing
│   ├── migrations/              # Database Schema Migrations
│   └── seeders/                 # Seeders for default departments, users, leaves, and logs
├── docs/
│   ├── api.md                   # REST API documentation
│   └── screenshots/             # Visual application screenshots
├── resources/views/             # Blade UI Views & Layouts
├── routes/
│   ├── api.php                  # API V1 Routes
│   └── web.php                  # Web Routes
└── tests/Feature/               # Feature Test Suites
```

---

## 🖼️ Screenshots

*Screenshots demonstrating web interfaces:*

- **Dashboard:** ![Dashboard](docs/screenshots/dashboard.png)
- **Employee Management:** ![Employees](docs/screenshots/employees.png)
- **Leave Requests & Balances:** ![Leave Requests](docs/screenshots/leave-requests.png)
- **Attendance & Check-in/out:** ![Attendance](docs/screenshots/attendance.png)
- **Monthly Attendance Report:** ![Reports](docs/screenshots/monthly-report.png)

---

## 🔮 Future Improvements

1. **Email Notifications:** Send email alerts to managers when new leave requests are submitted and notify employees upon approval/rejection.
2. **Holiday Calendar:** Support custom gazetted holidays and exclude company holidays from leave & attendance calculations.
3. **Multi-level Approval Workflow:** Support hierarchical approvals (Department Head -> HR Manager).
4. **PDF PaySlip & Attendance Summaries:** Generate downloadable PDF monthly attendance records for payroll processing.
