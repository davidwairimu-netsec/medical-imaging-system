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
