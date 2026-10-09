# HRM System REST API Documentation (`/api/v1`)

Welcome to the REST API documentation for the **HRM Leave & Attendance Management System**.

---

## Authentication & Headers

All API endpoints except `/login` require a Sanctum Bearer Token sent in the `Authorization` header.

### Required Headers
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <YOUR_ACCESS_TOKEN>
```

---

## 1. Authentication Endpoints

### 1.1 Login & Obtain Access Token
- **Endpoint:** `POST /api/v1/login`
- **Auth Required:** No

#### Request Example
```json
{
  "email": "employee1@hrm.test",
  "password": "password"
}
```

#### Success Response (`200 OK`)
```json
{
  "message": "Login successful",
  "access_token": "1|laravel_sanctum_token_string...",
  "token_type": "Bearer",
  "user": {
    "id": 3,
    "name": "Sarah Connor",
    "email": "employee1@hrm.test",
    "role": "employee",
    "designation": "Senior Full Stack Developer",
    "phone": "+1 555-0102",
    "join_date": "2023-06-10",
    "department": {
      "id": 2,
      "name": "Engineering & Development",
      "code": "ENG"
    }
  }
}
```

#### Error Response (`401 Unauthorized`)
```json
{
  "message": "The provided credentials do not match our records."
}
```

---

### 1.2 Logout User
- **Endpoint:** `POST /api/v1/logout`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`200 OK`)
```json
{
  "message": "Successfully logged out."
}
```

---

### 1.3 Get Authenticated Profile
- **Endpoint:** `GET /api/v1/me`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`200 OK`)
```json
{
  "data": {
    "id": 3,
    "name": "Sarah Connor",
    "email": "employee1@hrm.test",
    "role": "employee",
    "designation": "Senior Full Stack Developer",
    "department": {
      "id": 2,
      "name": "Engineering & Development",
      "code": "ENG"
    }
  }
}
```

---

## 2. Leave Management Endpoints

### 2.1 Get Leave Balances
- **Endpoint:** `GET /api/v1/leave-balance`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`200 OK`)
```json
{
  "data": [
    {
      "leave_type": {
        "id": 1,
        "name": "Casual Leave",
        "code": "CASUAL",
        "max_days_per_year": 12
      },
      "max_days": 12,
      "approved_used": 0,
      "pending_days": 3,
      "remaining_balance": 12
    },
    {
      "leave_type": {
        "id": 2,
        "name": "Sick Leave",
        "code": "SICK",
        "max_days_per_year": 10
      },
      "max_days": 10,
      "approved_used": 2,
      "pending_days": 0,
      "remaining_balance": 8
    }
  ]
}
```

---

### 2.2 List Leave Requests (Paginated)
- **Endpoint:** `GET /api/v1/leave-requests`
- **Auth Required:** Yes (`Bearer Token`)
- **Query Params (Optional):** `status`, `leave_type_id`, `user_id`, `page`

#### Success Response (`200 OK`)
```json
{
  "data": [
    {
      "id": 1,
      "user": { "id": 3, "name": "Sarah Connor" },
      "leave_type": { "id": 2, "name": "Sick Leave", "code": "SICK" },
      "from_date": "2026-10-10",
      "to_date": "2026-10-12",
      "total_days": 3,
      "reason": "Medical rest and recovery.",
      "status": "pending",
      "approver": null,
      "admin_comment": null
    }
  ],
  "links": { "first": "...", "last": "..." },
  "meta": { "current_page": 1, "total": 1 }
}
```

---

### 2.3 Submit Leave Request
- **Endpoint:** `POST /api/v1/leave-requests`
- **Auth Required:** Yes (`Bearer Token`)

#### Request Example
```json
{
  "leave_type_id": 1,
  "from_date": "2026-10-20",
  "to_date": "2026-10-22",
  "reason": "Family event in native town."
}
```

#### Success Response (`201 Created`)
```json
{
  "message": "Leave request submitted successfully.",
  "data": {
    "id": 5,
    "from_date": "2026-10-20",
    "to_date": "2026-10-22",
    "total_days": 3,
    "status": "pending"
  }
}
```

#### Error Response (`422 Unprocessable Entity`)
```json
{
  "message": "Insufficient leave balance. You requested 3 day(s), but only have 1 day(s) available for Casual Leave.",
  "errors": {
    "leave_type_id": [
      "Insufficient leave balance."
    ]
  }
}
```

---

### 2.4 Cancel Pending Leave Request
- **Endpoint:** `POST /api/v1/leave-requests/{id}/cancel`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`200 OK`)
```json
{
  "message": "Leave request cancelled successfully.",
  "data": {
    "id": 5,
    "status": "cancelled"
  }
}
```

---

### 2.5 Approve Leave Request (Manager / Admin Only)
- **Endpoint:** `POST /api/v1/leave-requests/{id}/approve`
- **Auth Required:** Yes (`Bearer Token` with Admin or Manager Role)

#### Request Example
```json
{
  "admin_comment": "Approved. Enjoy your vacation."
}
```

#### Success Response (`200 OK`)
```json
{
  "message": "Leave request approved successfully.",
  "data": {
    "id": 5,
    "status": "approved",
    "admin_comment": "Approved. Enjoy your vacation.",
    "approver": { "id": 2, "name": "Alex Morgan", "role": "manager" }
  }
}
```

---

### 2.6 Reject Leave Request (Manager / Admin Only)
- **Endpoint:** `POST /api/v1/leave-requests/{id}/reject`
- **Auth Required:** Yes (`Bearer Token` with Admin or Manager Role)

#### Request Example
```json
{
  "admin_comment": "Critical sprint deadline. Please reschedule."
}
```

#### Success Response (`200 OK`)
```json
{
  "message": "Leave request rejected successfully.",
  "data": {
    "id": 5,
    "status": "rejected",
    "admin_comment": "Critical sprint deadline. Please reschedule."
  }
}
```

---

## 3. Attendance Management Endpoints

### 3.1 Daily Check-In
- **Endpoint:** `POST /api/v1/attendance/check-in`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`201 Created`)
```json
{
  "message": "Check-in successful.",
  "data": {
    "id": 12,
    "date": "2026-10-09",
    "check_in": "2026-10-09T09:15:00+05:30",
    "check_out": null,
    "status": "present"
  }
}
```

#### Duplicate Check-In Error Response (`422 Unprocessable Entity`)
```json
{
  "message": "You have already checked in today at 09:15 AM."
}
```

---

### 3.2 Daily Check-Out
- **Endpoint:** `POST /api/v1/attendance/check-out`
- **Auth Required:** Yes (`Bearer Token`)

#### Success Response (`200 OK`)
```json
{
  "message": "Check-out successful.",
  "data": {
    "id": 12,
    "date": "2026-10-09",
    "check_in": "2026-10-09T09:15:00+05:30",
    "check_out": "2026-10-09T17:30:00+05:30",
    "work_duration": "8h 15m",
    "status": "present"
  }
}
```

---

### 3.3 List Attendance Records (Paginated)
- **Endpoint:** `GET /api/v1/attendance`
- **Auth Required:** Yes (`Bearer Token`)
- **Query Params (Optional):** `date`, `user_id`, `department_id`, `page`

#### Success Response (`200 OK`)
```json
{
  "data": [
    {
      "id": 12,
      "date": "2026-10-09",
      "check_in": "2026-10-09T09:15:00+05:30",
      "check_out": "2026-10-09T17:30:00+05:30",
      "work_duration": "8h 15m",
      "status": "present"
    }
  ],
  "meta": { "current_page": 1, "total": 1 }
}
```
