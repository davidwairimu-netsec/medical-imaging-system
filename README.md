# Digital Medical Imaging Management System

**Design and Implementation of a Digital Medical Imaging Management System for Improving the Storage, Retrieval, and Integrity of Patient Scan Records in Regional Hospitals**

---

## Project Overview

A working academic prototype of a **Digital Medical Imaging Management System** — a centralised platform for managing patient medical imaging records.

**Problems addressed:**
- Missing medical scan records
- Duplicate image records
- Inconsistent patient information
- Difficulty retrieving stored scans
- Unauthorised access
- Lack of proper audit trails
- Poor record organisation
- Inadequate backup management

> **Scope:** Imaging record management system only — NOT a full hospital management system.

---

## Author

**Wairimu David Kariuki**  
Department of ICT and Engineering

---

## System Status

| Item | Value |
|------|-------|
| Status | ✅ Working — All features tested |
| Installation Path | `/opt/lampp/htdocs/System 2/` |
| Access URL | `http://localhost/System%202/login.php` |
| Database | `medical_imaging_db` (MySQL/MariaDB) |
| PHP Version | 8.0+ |

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+ |
| Database | MySQL 8+ / MariaDB 10.4+ (InnoDB) |
| Server | Apache (XAMPP) |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Charts | Chart.js 4 |
| Icons | Bootstrap Icons |
| Database Access | PDO with prepared statements |
| Architecture | MVC-inspired modular |

---

## User Roles (7)

| Role | Access Level |
|------|--------------|
| Admin | Full system access |
| Head Nurse | Nursing module + patient viewing |
| Nurse | Read-only, assigned patients only |
| Radiologist | Imaging + patient viewing |
| Technician | Imaging + patient registration |
| Doctor | View-only across patients |
| Records Officer | Patient registration |

---

## Features

### Authentication & Security
- bcrypt password hashing (cost 10)
- CSRF protection on all POST forms
- Session management (HttpOnly, SameSite=Strict)
- Account lockout after 5 failed attempts
- Session timeout (30 min idle)
- Change Password (all users)
- Forgot Password with SHA-256 token (1-hour expiry)
- Email enumeration protection

### Role-Based Access Control
- 7 roles with granular permissions
- Server-side checks on every action
- Sidebar links conditionally rendered
- Direct URL access prevention
- 403 Access Denied page
- Least-privilege principle enforced

### Patient Management
- Register/edit/archive patients
- Unique hospital number validation
- Enhanced duplicate detection (exact/high/medium confidence)
- Search by name, hospital no., ID, phone

### Medical Imaging
- JPEG/PNG upload (up to 10 MB)
- SHA-256 duplicate detection
- X-ray, CT, MRI, Ultrasound support
- Multi-layer file validation
- Random secure filenames (32 hex chars)
- Image viewer with zoom, rotate, reset

### Deletion Workflow
- Admin: direct deletion with required reason
- Technician: request deletion (needs admin approval)
- Reason mandatory (min 10 chars)
- Full audit trail

### Archived Images
- Recently Deleted tab (last 200 with reasons)
- Archived Images tab (all, with search)
- Restore functionality

### Storage Integrity
- StorageIntegrity class verifies DB ↔ disk
- Detects missing/empty/orphaned files
- serve() checks file exists before readfile
- Admin dashboard with archive actions

### Audit Logging
- Comprehensive event tracking
- SHA-256 hash chain for tamper detection
- Audit Chain admin page

### Backup & Restore
- Full backup: DB dump + images + manifest
- SHA-256 checksum verification
- Restore with RESTORE confirmation

### Head Nurse Module
- Nursing dashboard with statistics
- Manage nurses (add/edit)
- Create login for nurses
- Reset nurse passwords
- Nurse assignments + workload + shifts

### Nurse Portal (Read-Only)
- Nurses log in with own credentials
- My Patients: only assigned patients
- View demographics, medications, imaging
- Cannot add, edit, or delete
- Unauthorized access logged

---

## Database Tables (15)

**Core:** roles, users, patients, medical_images, audit_logs, backups

**Nursing:** departments, nurses, nurse_doctor_assignments, nurse_patient_assignments, nurse_shifts, nurse_attendance

**Additional:** medications, password_resets, deletion_requests

---

## System Architecture
Presentation Layer (HTML5 / CSS3 / JavaScript / Bootstrap 5)
↓
Application Layer (PHP Controllers + Core Services)
↓
Data Access Layer (PDO Models with prepared statements)
↓
Database Layer (MySQL/MariaDB with InnoDB)


---

## Installation (Linux)

```bash
# 1. Clone
cd /opt/lampp/htdocs
sudo git clone https://github.com/davidwairimu-netsec/medical-imaging-system.git "System 2"
sudo chown -R $USER:$USER "System 2"

# 2. Permissions
cd "System 2"
chmod -R 777 uploads backups logs

# 3. Database
/opt/lampp/bin/mysql -u root -e "CREATE DATABASE medical_imaging_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
/opt/lampp/bin/mysql -u root medical_imaging_db < database/database.sql

# 4. Extended schema (nursing, auth, integrity tables)

# 5. Start XAMPP
sudo /opt/lampp/lampp start
Open: http://localhost/System%202/login.php

Demo Credentials
Role	Username	Password
Administrator	admin	ChangeMe123!
Head Nurse	headnurse	ChangeMe123!
Nurse	nurse	ChangeMe123!
Radiologist	radiologist	ChangeMe123!
Technician	technician	ChangeMe123!
Doctor	doctor	ChangeMe123!
Records Officer	records	ChangeMe123!
⚠️ Change all demo passwords before deployment.

Security Features
Feature	Implementation
Password Storage	bcrypt cost 10
SQL Injection	PDO prepared statements
XSS	htmlspecialchars()
CSRF	Token verification
Session Security	HttpOnly, SameSite=Strict
File Upload	Multi-layer validation
Filename Handling	Random 32-hex names
Access Control	RBAC server-side
Audit Trail	SHA-256 hash chain
Account Lockout	5 failures = locked
File Storage	Outside web root, PHP disabled
Testing Checklist
Authentication
☑ Valid/invalid login
☑ Logout destroys session
☑ Session timeout works
☑ Account locks after 5 failures
☑ Change + forgot password
Authorisation
☑ Role-based sidebar
☑ URL manipulation blocked
☑ 403 page works
☑ Nurse cannot access general patients
Imaging
☑ Upload validation
☑ Duplicate detection
☑ Delete workflow
☑ Restore from archive
Security
☑ SQL injection blocked
☑ XSS escaped
☑ CSRF required
☑ Upload directory safe
Nursing
☑ Nurse management
☑ Login creation
☑ Password reset
☑ Read-only enforcement
Troubleshooting
Issue	Solution
DB connection failed	Use 127.0.0.1 not localhost
Upload fails	chmod 777 uploads/
Backup fails	Check /opt/lampp/bin/mysqldump
403 errors	Check role in core/Auth.php
Login fails	Regenerate password hashes
Empty dropdowns	Insert sample data
Limitations
No DICOM support (JPEG/PNG only)

Not a diagnostic viewer

No cloud storage

No HL7/FHIR interoperability

No encryption at rest

Manual backups only

No 2FA

Single-tenant

Academic prototype only

Future Improvements
Full DICOM support + PACS integration

HL7/FHIR interoperability

Cloud storage

Advanced image viewer

Mobile application

Multi-hospital support

Automated backups

Two-factor authentication

Encryption at rest + in transit

EHR integration

AI-assisted classification

Security & Ethics Warning
This is an academic healthcare prototype. NOT for production clinical use.

Use fictional patient data only.

License
Academic project — evaluation and demonstration only. Not licensed for production use.

End of README