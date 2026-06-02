
## 1. PROJECT ROLE (AI INSTRUCTION)

Siz Laravel 12 backend developer assistantisiz.

QOIDALAR:
- Laravel 12.0 ishlatiladi
- PHP 8.2+
- Sanctum authentication majburiy
- Service-based architecture ishlatiladi
- Controller faqat request/response uchun
- Business logic Service layerda bo‘ladi
- JSON response standart bo‘lishi shart
- Role-based access control: admin / member

---

## 2. BUSINESS DOMAIN

TaskFlow — jamoaviy task management tizimi.

ASOSIY G‘OYA:
- Admin task yaratadi
- Member task bajaradi
- Task tugaganda ball beriladi
- Issue orqali muammolar qayd qilinadi
- Comment orqali muloqot bo‘ladi

---

## 3. ROLES

### ADMIN
- Team boshqaradi
- Task yaratadi
- Task assign qiladi
- Issue resolve qiladi
- Member qo‘shadi/o‘chiradi

### MEMBER
- Task bajaradi
- Progress yangilaydi
- Issue yaratadi
- Comment yozadi

---

## 4. DATABASE STRUCTURE

### teams
- id
- name
- slug

---

### users
- id
- team_id
- name
- username
- email
- password
- role: admin | member
- avatar (nullable)
- ball (default 0)
- last_seen_at (nullable)
- online_minutes (default 0)

---

### tasks
- id
- team_id
- created_by
- assigned_to (nullable)
- title
- description
- status: pending | in_progress | done | cancelled
- priority: low | medium | high
- progress: 0 - 100
- due_date (nullable)
- completed_at (nullable)

---

### issues
- id
- task_id
- created_by
- assigned_to
- title
- description
- severity: low | medium | high | critical
- status: open | in_progress | resolved
- resolved_at (nullable)

---

### comments
- id
- task_id
- user_id
- body
- created_at
- updated_at

---

### attachments (polymorphic)
- id
- attachable_type
- attachable_id
- file_path
- file_name
- file_size
- mime_type

---

### ball_logs
- id
- user_id
- task_id
- action: task_completed | issue_resolved | manual_adjust
- ball
- created_at

---

## 5. API RESPONSE FORMAT

### SUCCESS RESPONSE
```json
{
  "success": true,
  "message": "OK",
  "data": {}
}
````

### ERROR RESPONSE

```json
{
  "success": false,
  "message": "Error message",
  "errors": {}
}
```

---

## 6. AUTH FLOW

### REGISTER

* yangi team yaratiladi
* admin user avtomatik yaratiladi

### LOGIN

* team_name + username + password

### TOKEN

* Laravel Sanctum Bearer token

---

## 7. TASK BUSINESS RULES

* Task faqat admin yaratadi
* Task memberga assign qilinadi
* Member progress update qiladi
* Agar status = done bo‘lsa:

  * ball beriladi
  * ball_logs yoziladi
  * completed_at set qilinadi

---

## 8. ISSUE BUSINESS RULES

* Member issue yaratadi
* Admin yoki Member issue resolve qiladi
* Resolve bo'lganligini Admin Checked qiladi checked bo‘lsa:

  * optional ball bonus beriladi

---

## 9. ROLE MIDDLEWARE

* auth:sanctum majburiy
* role tekshiriladi:

  * admin routes
  * member routes

---

## 10. SERVICES LAYER

### AuthService

* register()
* login()

### MemberService

* index()
* store()
* show()
* update()
* destroy()

### TaskService

* create()
* assign()
* updateProgress()
* complete()
* index()
* show()
* update()
* destroy()

### IssueService (rejalashtirilgan)

* create()
* resolve()
* checkedResolve()

---

## 11. API ENDPOINTS

### AUTH

* POST /api/v1/auth/register
* POST /api/v1/auth/login
* GET /api/v1/auth/me
* POST /api/v1/auth/logout

---

### MEMBERS (ADMIN ONLY)

* GET /api/v1/members
* POST /api/v1/members
* GET /api/v1/members/{id}
* PUT /api/v1/members/{id}
* DELETE /api/v1/members/{id}

---

### TASKS

* GET /api/v1/tasks
* POST /api/v1/tasks (admin)
* PUT /api/v1/tasks/{id} (admin)
* DELETE /api/v1/tasks/{id} (admin)
* PATCH /api/v1/tasks/{id}/assign (admin)
* PATCH /api/v1/tasks/{id}/progress (member)
* PATCH /api/v1/tasks/{id}/complete (admin)

---

### ISSUES

* GET /api/v1/tasks/{id}/issues
* POST /api/v1/tasks/{id}/issues
* PATCH /api/v1/issues/{id}/resolve (all)
* POST /api/v1/tasks/{id}/issues/checked



---

### COMMENTS

* GET /api/v1/tasks/{id}/comments
* POST /api/v1/tasks/{id}/comments

---

### ATTACHMENTS

* POST /api/v1/tasks/{id}/attachments
* DELETE /api/v1/attachments/{id}

---

### LEADERBOARD (FUTURE)

* GET /api/v1/leaderboard/daily
* GET /api/v1/leaderboard/weekly
* GET /api/v1/leaderboard/monthly

---

### STATS (FUTURE - ADMIN ONLY)

* GET /api/v1/stats/overview
* GET /api/v1/stats/users
* GET /api/v1/stats/tasks

---

## 12. PROJECT STRUCTURE

app/
├── Http/
│   ├── Controllers/Api/
│   ├── Middleware/
│   └── Resources/
├── Models/
├── Services/
├── DTO/ (optional)
├── Actions/ (optional)
└── Rules/ (optional)

database/
├── migrations/
├── seeders/
└── factories/

routes/
└── api.php

---

## 13. IMPORTANT AI RULES

* Controller = thin layer
* Service = business logic
* Model = only relations
* Always validate request
* Always return JSON format
* Never bypass RoleMiddleware
* Keep architecture clean and modular

---

## 14. FUTURE FEATURES

* Real-time notifications (WebSocket)
* Leaderboard system
* File storage (S3/local)
* Activity tracking
* Online time tracking
* AI task assistant
