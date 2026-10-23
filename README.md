# 🏥 MediImaging DMS - Digital Medical Imaging Management System

A secure, forensically-ready web application designed to manage patient scan records, prevent data duplication, and ensure cryptographic data integrity in regional hospitals.

## 🚀 Live Demo

You can view the live deployment of the system here:
**[https://digitalmedical.byethost4.com/](https://digitalmedical.byethost4.com/)**

> **Note:** This is a live deployment. Authorised access only. All activity is monitored and logged.

## 🔐 Core Security & Forensic Features

This system was built with security and digital forensics at its core, addressing the problem of missing, duplicated, and tampered medical records.

*   **Cryptographic Duplicate Detection:** Uses SHA-256 content hashing to prevent identical images from being uploaded twice, regardless of filename changes.
*   **Tamper-Evident Audit Logs:** Every action (logins, uploads, views, deletions) is recorded in a hash-chained audit trail. Any modification to historical logs breaks the cryptographic chain and is instantly detectable.
*   **Forensic Integrity Checker:** A dedicated script (`scripts/forensic_check.php`) that compares stored database hashes against physical files on the disk to detect unauthorized tampering.
*   **Role-Based Access Control (RBAC):** Distinct interfaces and permissions for Administrators, Radiologists, Doctors, Head Nurses, and Nurses.
*   **Secure Uploads:** Validates MIME types, file extensions, and uses `getimagesize()` to prevent malicious file uploads.
*   **Storage Integrity Audit:** An admin dashboard that cross-references the database with the file system to identify missing, orphaned, or empty files.

## 🛠️ Technology Stack

*   **Backend:** PHP 8.2 (MVC-inspired architecture)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
*   **Security:** PDO Prepared Statements, Bcrypt Password Hashing, CSRF Tokens, SHA-256 Hashing
*   **Environment:** XAMPP (Local Development), ByetHost (Live Deployment)

## 📂 Project Structure

```text
├── config/          # Database and application configuration
├── controllers/     # Request handling and logic orchestration
├── core/            # Core classes (Auth, CSRF, Database, StorageIntegrity, AuditLog)
├── models/          # Data access layer
├── views/           # UI templates (role-specific dashboards)
├── scripts/         # Forensic check and maintenance scripts
├── database/        # SQL schema and seed scripts
├── docs/            # Project documentation, DFDs, and screenshots
└── public/          # Static assets (CSS, JS, uploads)
⚙️ Local Setup Instructions
To run this project locally on your machine:

<<<<<<< HEAD
| Item | Value |
|------|-------|
| Status | ✅ Working — All features tested |
| Access URL | `[https://digitalmedical.byethost4.com/]` |
| Database | `medical_imaging_db` (MySQL/MariaDB) |
| PHP Version | 8.0+ |
=======
Clone the repository:
>>>>>>> dc1e5a2 (docs: add author profile and update title to Security Engineer Practitioner)

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

🧪 Forensic Verification
To test the forensic integrity of the system locally, run the standalone detection script:

bash
php scripts/forensic_check.php
This will output the status of every image (OK, MISSING, or TAMPERED).

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

### How to update your repository:

Run the following commands in your terminal to update your local `README.md` and push it to GitHub:

```bash
cd "/opt/lampp/htdocs/System 2"

# Overwrite the README with the updated content
cat > README.md << 'EOF'
# 🏥 MediImaging DMS - Digital Medical Imaging Management System

A secure, forensically-ready web application designed to manage patient scan records, prevent data duplication, and ensure cryptographic data integrity in regional hospitals.

## 🚀 Live Demo

You can view the live deployment of the system here:
**[https://digitalmedical.byethost4.com/](https://digitalmedical.byethost4.com/)**

> **Note:** This is a live deployment. Authorised access only. All activity is monitored and logged.

## 🔐 Core Security & Forensic Features

This system was built with security and digital forensics at its core, addressing the problem of missing, duplicated, and tampered medical records.

*   **Cryptographic Duplicate Detection:** Uses SHA-256 content hashing to prevent identical images from being uploaded twice, regardless of filename changes.
*   **Tamper-Evident Audit Logs:** Every action (logins, uploads, views, deletions) is recorded in a hash-chained audit trail. Any modification to historical logs breaks the cryptographic chain and is instantly detectable.
*   **Forensic Integrity Checker:** A dedicated script (`scripts/forensic_check.php`) that compares stored database hashes against physical files on the disk to detect unauthorized tampering.
*   **Role-Based Access Control (RBAC):** Distinct interfaces and permissions for Administrators, Radiologists, Doctors, Head Nurses, and Nurses.
*   **Secure Uploads:** Validates MIME types, file extensions, and uses `getimagesize()` to prevent malicious file uploads.
*   **Storage Integrity Audit:** An admin dashboard that cross-references the database with the file system to identify missing, orphaned, or empty files.

## 🛠️ Technology Stack

*   **Backend:** PHP 8.2 (MVC-inspired architecture)
*   **Database:** MySQL / MariaDB
*   **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5
*   **Security:** PDO Prepared Statements, Bcrypt Password Hashing, CSRF Tokens, SHA-256 Hashing
*   **Environment:** XAMPP (Local Development), ByetHost (Live Deployment)

## 📂 Project Structure

```text
├── config/          # Database and application configuration
├── controllers/     # Request handling and logic orchestration
├── core/            # Core classes (Auth, CSRF, Database, StorageIntegrity, AuditLog)
├── models/          # Data access layer
├── views/           # UI templates (role-specific dashboards)
├── scripts/         # Forensic check and maintenance scripts
├── database/        # SQL schema and seed scripts
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

🧪 Forensic Verification
To test the forensic integrity of the system locally, run the standalone detection script:

bash
php scripts/forensic_check.php
This will output the status of every image (OK, MISSING, or TAMPERED).

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
EOF

<<<<<<< HEAD
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
=======
Stage, commit, and push the changes
git add README.md
git commit -m "docs: add author profile and update title to Security Engineer Practitioner"
git push origin main
>>>>>>> dc1e5a2 (docs: add author profile and update title to Security Engineer Practitioner)
