# Digital Medical Imaging Management System

**Design and Implementation of a Digital Medical Imaging Management System for Improving the Storage, Retrieval, and Integrity of Patient Scan Records in Regional Hospitals**

---

## Project Overview

This is a **working academic prototype** of a Digital Medical Imaging Management System. It provides a centralised platform for managing patient medical imaging records, addressing the key problems of:

- Missing medical scan records
- Duplicate image records
- Inconsistent patient information
- Difficulty retrieving stored scans
- Unauthorised access
- Lack of proper audit trails
- Poor record organisation
- Inadequate backup management

> **IMPORTANT:** This system is an **imaging record management system**, NOT a complete hospital management system. It does NOT include pharmacy, billing, laboratory, inpatient administration, or automated medical diagnosis.

---

## Objectives

1. Improve storage of patient scan records
2. Improve retrieval of patient scan records
3. Reduce duplicate image records using SHA-256 hash detection
4. Improve patient information consistency
5. Improve security and access control through RBAC
6. Provide auditability through comprehensive logging
7. Improve data integrity through database constraints
8. Provide backup management

---

## Technology Stack

| Layer | Technology |
|-------|-----------|
| Backend | PHP 8+ |
| Database | MySQL 8+ (InnoDB) |
| Server | Apache (XAMPP) |
| Frontend | HTML5, CSS3, JavaScript, Bootstrap 5 |
| Charts | Chart.js 4 |
| Icons | Bootstrap Icons |
| Database Access | PDO with prepared statements |

---

## Features

### Core Modules

1. **Authentication** — Secure login with bcrypt hashing, session management, CSRF protection, and account lockout.
2. **Role-Based Access Control** — Five roles (Admin, Radiologist, Technician, Doctor, Records Officer) with least-privilege permissions.
3. **Patient Management** — Register, view, edit, search, archive patients.
4. **Medical Imaging** — Upload, store, retrieve, view, download, archive medical images.
5. **Duplicate Detection** — SHA-256 hash comparison prevents duplicate image records.
6. **Image Viewer** — Zoom, rotate, reset, fit-to-screen controls.
7. **Patient Imaging History** — Chronological view of all patient scans.
8. **Advanced Search** — Multi-criteria search with pagination.
9. **Audit Logging** — Comprehensive audit trail of all system actions.
10. **Backup Management** — Database backup creation and download.
11. **Dashboard** — Statistics, charts, recent activity, system health.

---

## System Architecture

Presentation Layer (HTML/CSS/JS/Bootstrap 5)
↓
Application Layer (PHP Controllers)
↓
Data Access Layer (PDO Models)
↓
Database Layer (MySQL with InnoDB)


---

## Database Structure

| Table | Purpose |
|-------|---------|
| `roles` | Role definitions |
| `users` | System users |
| `patients` | Patient records |
| `medical_images` | Imaging metadata |
| `audit_logs` | Security audit trail |
| `backups` | Backup history |

### Key Relationships

- One user belongs to one role
- One user can upload many medical images
- One patient can have many medical images
- One user can create many audit log entries

---

## Installation

### Prerequisites

- Windows/Linux/macOS
- XAMPP (or equivalent with Apache + MySQL + PHP 8+)
- Web browser

### Step-by-Step

1. **Install XAMPP**
   - Download from https://www.apachefriends.org/
   - Install and start Apache and MySQL from the XAMPP Control Panel.

2. **Copy Project Files**

Copy the medical-imaging-system folder to:
C:\xampp\htdocs\medical-imaging-system


3. **Create Database**
- Open http://localhost/phpmyadmin
- Click "Import"
- Select `database/database.sql`
- Click "Go"

4. **Generate Password Hashes**
- Visit http://localhost/medical-imaging-system/database/setup_hashes.php
- Copy the generated SQL UPDATE statements
- Run them in phpMyAdmin's SQL tab

5. **Configure Database** (if needed)
- Edit `config/database.php` with your MySQL credentials
- Default XAMPP: username `root`, password empty

6. **Set Directory Permissions**
- Ensure `uploads/`, `backups/`, and `logs/` are writable

7. **Open Application**

http://localhost/medical-imaging-system/


---

## Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Administrator | admin | ChangeMe123! |
| Radiologist | radiologist | ChangeMe123! |
| Technician | technician | ChangeMe123! |
| Doctor | doctor | ChangeMe123! |
| Records Officer | records | ChangeMe123! |

> **CRITICAL:** Change all demo passwords before any real deployment!

---

## Security Features

| Feature | Implementation |
|---------|---------------|
| Password Storage | bcrypt with cost 10 |
| SQL Injection | PDO prepared statements everywhere |
| XSS | HTML escaping on all output |
| CSRF | Token-based verification on POST |
| Session Security | HttpOnly, SameSite=Strict, regeneration |
| File Upload | Extension whitelist, MIME validation, SHA-256 |
| Access Control | Role-based permission checks |
| Audit Trail | Comprehensive logging |
| Account Lockout | 5 failed attempts = locked |
| File Storage | Outside web root, no execution |

---

## Duplicate Detection

The system calculates a **SHA-256 hash** of every uploaded image file. Before accepting an upload:

1. Hash is computed from file contents
2. Database is searched for existing identical hash
3. If duplicate found:
- Upload is blocked
- Existing record is shown
- Attempt is logged in audit trail
4. Only unique images are stored

This ensures **exact duplicate images** are detected. Note: this detects identical files, not visually similar images.

---

## Backup Process

Administrators can create database backups from the Backup Management page. The system uses `mysqldump` to export the database.

> **Production Note:** For real clinical deployment, use automated backup infrastructure with off-site storage, encryption, and tested recovery procedures. This prototype's backup function is for demonstration.

---

## Testing Checklist

### Authentication
- [ ] Valid login redirects to dashboard
- [ ] Invalid login shows error
- [ ] Logout destroys session
- [ ] Disabled account cannot log in
- [ ] Session timeout works

### Authorisation
- [ ] Admin can access all pages
- [ ] Doctor cannot access user management
- [ ] Technician cannot access audit logs
- [ ] URL manipulation is blocked

### Patient Management
- [ ] Create patient
- [ ] Edit patient
- [ ] Search patient
- [ ] Duplicate patient warning
- [ ] Archive patient

### Imaging
- [ ] Upload valid image
- [ ] Reject invalid file type
- [ ] Reject oversized file
- [ ] Duplicate image warning
- [ ] View image in viewer
- [ ] Download image
- [ ] Archive image

### Security
- [ ] SQL injection attempt fails
- [ ] XSS attempt is escaped
- [ ] CSRF token required
- [ ] Direct file access blocked

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection failed | Check `config/database.php` credentials |
| Upload fails | Ensure `uploads/` is writable |
| Backup fails | Ensure `mysqldump` is in system PATH |
| Session issues | Clear browser cookies |
| 403 errors | Check role permissions |

---

## Limitations

1. **No DICOM support** — Only JPEG/PNG. DICOM is a future enhancement.
2. **Not a diagnostic viewer** — Image viewer is academic prototype only.
3. **No cloud storage** — Local file storage only.
4. **No HL7/FHIR** — No interoperability with other systems.
5. **No encryption at rest** — Files stored unencrypted.
6. **Manual backup only** — No automated scheduling.
7. **No 2FA** — Single-factor authentication only.

---

## Future Improvements

- Full DICOM support
- PACS integration
- HL7/FHIR interoperability
- Cloud storage (AWS S3, Azure Blob)
- Advanced image viewer (windowing, measurements)
- Mobile application
- Multi-hospital support
- Automated backup scheduling
- Two-factor authentication
- Encryption at rest and in transit
- Electronic Health Record integration
- AI-assisted image classification

---

## Security & Ethics Warning

This is an **academic healthcare prototype**. Do NOT claim it is suitable for production clinical deployment.

Real-world deployment would require:

- Formal security assessment
- Data protection compliance (GDPR, HIPAA, Kenya DPA)
- Clinical governance
- Professional infrastructure
- Secure networking
- Encryption (at rest and in transit)
- Backup and disaster recovery
- Access governance
- Security monitoring
- Regulatory approval
- Clinical validation

**Use fictional patient data only.** Never upload real patient information to this prototype.

---

## License

Academic project — Zetech University, Department of ICT and Engineering.

---

## Author

**Wairimu David Kariuki**  
Registration Number: DCF-01-0247/2025  
Zetech University


---

## Demo Credentials

| Role | Username | Password |
|------|----------|----------|
| Administrator | admin | ChangeMe123! |
| Radiologist | radiologist | ChangeMe123! |
| Technician | technician | ChangeMe123! |
| Doctor | doctor | ChangeMe123! |
| Records Officer | records | ChangeMe123! |

> **CRITICAL:** Change all demo passwords before any real deployment!

---

## Security Features

| Feature | Implementation |
|---------|---------------|
| Password Storage | bcrypt with cost 10 |
| SQL Injection | PDO prepared statements everywhere |
| XSS | HTML escaping on all output |
| CSRF | Token-based verification on POST |
| Session Security | HttpOnly, SameSite=Strict, regeneration |
| File Upload | Extension whitelist, MIME validation, SHA-256 |
| Access Control | Role-based permission checks |
| Audit Trail | Comprehensive logging |
| Account Lockout | 5 failed attempts = locked |
| File Storage | Outside web root, no execution |

---

## Duplicate Detection

The system calculates a **SHA-256 hash** of every uploaded image file. Before accepting an upload:

1. Hash is computed from file contents
2. Database is searched for existing identical hash
3. If duplicate found:
- Upload is blocked
- Existing record is shown
- Attempt is logged in audit trail
4. Only unique images are stored

This ensures **exact duplicate images** are detected. Note: this detects identical files, not visually similar images.

---

## Backup Process

Administrators can create database backups from the Backup Management page. The system uses `mysqldump` to export the database.

> **Production Note:** For real clinical deployment, use automated backup infrastructure with off-site storage, encryption, and tested recovery procedures. This prototype's backup function is for demonstration.

---

## Testing Checklist

### Authentication
- [ ] Valid login redirects to dashboard
- [ ] Invalid login shows error
- [ ] Logout destroys session
- [ ] Disabled account cannot log in
- [ ] Session timeout works

### Authorisation
- [ ] Admin can access all pages
- [ ] Doctor cannot access user management
- [ ] Technician cannot access audit logs
- [ ] URL manipulation is blocked

### Patient Management
- [ ] Create patient
- [ ] Edit patient
- [ ] Search patient
- [ ] Duplicate patient warning
- [ ] Archive patient

### Imaging
- [ ] Upload valid image
- [ ] Reject invalid file type
- [ ] Reject oversized file
- [ ] Duplicate image warning
- [ ] View image in viewer
- [ ] Download image
- [ ] Archive image

### Security
- [ ] SQL injection attempt fails
- [ ] XSS attempt is escaped
- [ ] CSRF token required
- [ ] Direct file access blocked

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| Database connection failed | Check `config/database.php` credentials |
| Upload fails | Ensure `uploads/` is writable |
| Backup fails | Ensure `mysqldump` is in system PATH |
| Session issues | Clear browser cookies |
| 403 errors | Check role permissions |

---

## Limitations

1. **No DICOM support** — Only JPEG/PNG. DICOM is a future enhancement.
2. **Not a diagnostic viewer** — Image viewer is academic prototype only.
3. **No cloud storage** — Local file storage only.
4. **No HL7/FHIR** — No interoperability with other systems.
5. **No encryption at rest** — Files stored unencrypted.
6. **Manual backup only** — No automated scheduling.
7. **No 2FA** — Single-factor authentication only.

---

## Future Improvements

- Full DICOM support
- PACS integration
- HL7/FHIR interoperability
- Cloud storage (AWS S3, Azure Blob)
- Advanced image viewer (windowing, measurements)
- Mobile application
- Multi-hospital support
- Automated backup scheduling
- Two-factor authentication
- Encryption at rest and in transit
- Electronic Health Record integration
- AI-assisted image classification

---

## Security & Ethics Warning

This is an **academic healthcare prototype**. Do NOT claim it is suitable for production clinical deployment.

Real-world deployment would require:

- Formal security assessment
- Data protection compliance (GDPR, HIPAA, Kenya DPA)
- Clinical governance
- Professional infrastructure
- Secure networking
- Encryption (at rest and in transit)
- Backup and disaster recovery
- Access governance
- Security monitoring
- Regulatory approval
- Clinical validation

**Use fictional patient data only.** Never upload real patient information to this prototype.

---

## License

Academic project — Zetech University, Department of ICT and Engineering.

---

## Author

**Wairimu David Kariuki**  

Zetech University


