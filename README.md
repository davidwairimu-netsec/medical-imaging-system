# 🏥 MediImaging DMS - Digital Medical Imaging Management System

A secure, forensically-ready web application designed to manage patient scan records, prevent data duplication, and ensure cryptographic data integrity in regional hospitals.

## 🚀 Live Demo

You can view the live deployment of the system here:
**[https://digitalmedical.byethost4.com/](https://digitalmedical.byethost4.com/)**

> **Note:** This is a live deployment. Authorised access only. All activity is monitored and logged.

## 🛡️ Cybersecurity & Forensic Features

This system was built with security and digital forensics at its core, specifically addressing the problem of missing, duplicated, and tampered medical records.

*   **Cryptographic Duplicate Detection (SHA-256):** Prevents identical images from being uploaded twice by comparing the SHA-256 hash of the file content, not just the filename. This blocks users from overwriting medical errors with renamed files.
*   **Tamper-Evident Hash-Chained Audit Logs:** Every action (logins, uploads, views, deletions) is recorded in an `audit_logs` table. Each log entry's SHA-256 hash includes the hash of the previous entry, creating a cryptographic chain. If any historical log is altered, the chain breaks and is instantly detectable.
*   **Forensic Integrity Checker:** A dedicated forensic script (`scripts/forensic_check.php`) that cross-references database records with physical files on disk. It recomputes hashes to detect unauthorized tampering, missing files, or zero-byte files.
*   **Chain of Custody Enforcement:** The system logs the user ID, IP address, timestamp, and user agent for every state-changing action. This provides a complete timeline for forensic investigations.
*   **Role-Based Access Control (RBAC):** Enforces strict access control across 6 roles (Admin, Radiologist, Doctor, Head Nurse, Nurse, Lab Technician). Unauthorized URL access is blocked server-side with 403 errors and logged.
*   **Secure File Uploads:** Validates MIME types via `finfo`, checks extensions against a whitelist, and uses `getimagesize()` to prevent malicious scripts disguised as images.
*   **Session Hardening:** Implements `HttpOnly` and `SameSite=Strict` cookies, session ID regeneration on login, and CSRF tokens for all POST requests.

## 🛠️ Technology Stack

*   **Backend:** PHP 8.2 (MVC-inspired architecture)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
*   **Security:** PDO Prepared Statements, Bcrypt, CSRF Tokens, SHA-256 Hashing
*   **Environment:** XAMPP (Local Development), ByetHost (Live Deployment)

## 🧪 Forensic Verification & Integrity Auditing

To demonstrate the forensic readiness of the system, you can run the standalone integrity check:

```bash
php scripts/forensic_check.php
This script outputs the integrity status of every stored image ([OK], [MISSING], or [TAMPERED]).

Additionally, the Audit Chain Verification page (accessible in the Admin dashboard) iterates through the entire audit_logs table, recomputes the hash chain, and validates that 100% of the entries are cryptographically linked. In the case of legacy data, a backfill script is provided to repair broken chains without losing the historical audit trail.

📂 Project Structure
text
├── config/          # Environment and database configuration
├── controllers/     # Request handling and logic orchestration
├── core/            # Core classes (Auth, CSRF, Database, StorageIntegrity, AuditLog)
├── models/          # Data access layer
├── views/           # UI templates (role-specific dashboards)
├── scripts/         # Forensic check and maintenance scripts
├── database/        # SQL schema, seed, and backfill scripts
├── docs/            # Project documentation, DFDs, and screenshots
└── public/          # Static assets (CSS, JS, uploads)
⚙️ Local Setup Instructions
To run this project locally on your machine:

Clone the repository:

bash
git clone <your-repo-url>
cd "System 2"
Start XAMPP:
Ensure Apache and MySQL are running.

Database Setup:

Create a database named medical_imaging_db in phpMyAdmin.

Import the schema from database/database.sql.

Configure Environment:

Ensure config/database.php is set to use localhost and the root user.

(Note: The config file automatically detects CLI vs Browser environments).

Access the System:
Navigate to http://localhost/System%202/ in your browser.

📸 Documentation
The docs/ folder contains comprehensive documentation, including:

System Flowcharts

Data Flow Diagrams (Level 0 & 1)

Use Case & Class Diagrams

Attack/Investigation Scenario & Threat Model

Legal & Ethical Considerations

Step-by-step Forensic Demo Guide

👤 Author
Wairimu David Kariuki
Security Engineer Practitioner

📄 License
This project is licensed under the MIT License - see the LICENSE file for details.

Built to demonstrate secure coding practices, cryptographic integrity, and digital forensic readiness.

## 🛡️ Security Hardening — Brute-Force & Rate Limiting

The login system is hardened against brute-force attacks, credential stuffing, automated bots, and resource exhaustion using a multi-layer defence:

### 1. Per-Account Rate Limiting

- **Threshold**: 5 failed attempts within a sliding 15-minute window
- **Trigger**: The 6th attempt in that window is blocked, and all subsequent attempts are blocked until the window resets
- **Storage**: Every attempt is written to the isolated `login_attempts` table with the username, IP, timestamp, and success flag
- **User feedback**: `Too many failed login attempts. Please wait 15 minutes and try again.`
- **Why**: Prevents an attacker from trying thousands of password guesses against a known account

### 2. Per-IP Rate Limiting

- The same 5-in-15-minutes rule is applied to the **source IP address**, not just the account
- **Why**: Blocks a single attacker from spraying one password across many accounts (credential stuffing)

### 3. Distinct Authentication Responses

- **Unknown username** → `Invalid username. Please check your username and try again.`
- **Wrong password** → `Invalid password. Please try again.`
- **Rate-limited** → `Too many failed login attempts. Please wait 15 minutes and try again.`
- **Why**: The user gets clear, actionable guidance without exposing internal system details. Disabled and locked accounts also return the same "Invalid username" message (account enumeration prevention)

### 4. Secure Password Handling

- Passwords are **never stored in plaintext**, never logged, and never returned in error messages
- Password verification uses `password_verify()` against a **bcrypt** hash
- Successful logins reset the `failed_login_attempts` counter on the user row
- **Why**: Even if the database is compromised, the raw passwords cannot be recovered

### 5. Server-Side Validation

- All credential validation is performed server-side
- CSRF tokens are enforced on every POST request
- Sessions are regenerated on successful login (with CSRF token preserved)
- Session cookies use `HttpOnly` + `SameSite=Strict`
- **Why**: Blocks credential replay, CSRF, session fixation, and cross-site attacks

### 6. Forensic Audit Trail

Every attempt — successful or blocked — is written to `audit_logs`:

| Action | Meaning |
|--------|---------|
| `USER_LOGIN` | Successful authentication |
| `USER_LOGIN_FAILED` | Wrong password (`wrong_password`) or unknown user (`unknown_username`) |
| `USER_LOGIN_BLOCKED` | Attempt made while rate-limited |

Each entry records: user ID (or NULL), action, description, IP address, and timestamp — **never the password**.

### 7. DoS / Crash Prevention

- Rate limiting prevents the server from processing an unbounded number of password-hash verifications per unit of time
- Each bcrypt verification is intentionally expensive; rate limiting ensures the CPU cost stays bounded under attack
- The `login_attempts` table uses indexes on `username`, `ip_address`, and `attempt_time` to keep lookups fast even as the table grows
- **Why**: An unauthenticated attacker cannot exhaust the CPU or stall the app by hammering the login endpoint

### Rate Limit Configuration

| Parameter | Default | Location |
|-----------|---------|----------|
| Max failed attempts | 5 | `core/Auth.php` — `$maxAttempts` |
| Lockout window | 15 minutes | `core/Auth.php` — `$lockoutMinutes` |
| Per-attempt time | bcrypt cost | `password_hash()` / `password_verify()` |

### Testing the Rate Limiter

1. Open the login page
2. Enter `admin` / `wrongpass` and submit — repeat 6 times
3. On the 6th attempt: **`Too many failed login attempts. Please wait 15 minutes and try again.`**
4. Verify in DB:

```sql
SELECT username, success, attempt_time 
FROM login_attempts 
WHERE username = 'admin'
ORDER BY attempt_id DESC;
5. Verify the audit trail:

sql
SELECT action, description, ip_address, created_at
FROM audit_logs
WHERE action = 'USER_LOGIN_BLOCKED'
ORDER BY log_id DESC;
Resetting the Rate Limit (for testing)
bash
/opt/lampp/bin/mysql -u root medical_imaging_db -e "
DELETE FROM login_attempts WHERE username = 'admin' AND success = 0;
"
Or wait 15 minutes for the window to slide.

