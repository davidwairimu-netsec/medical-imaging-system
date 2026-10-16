# FORENSIC DEMONSTRATION — SCREENSHOT INDEX
## DCF-01-0247/2025 - Wairimu David Kariuki

This document indexes the screenshots captured during the live Milestone 3 demonstration.

---

## Screenshot Index

| # | Filename | Description |
|---|----------|-------------|
| 1 | Screenshot_01_Dashboard.png | System dashboard after login as admin |
| 2 | Screenshot_02a_Forensic_Script.png | Forensic script — header and setup (Part 1/3) |
| 3 | Screenshot_02b_Forensic_Script.png | Forensic script — core logic (Part 2/3) |
| 4 | Screenshot_02c_Forensic_Script.png | Forensic script — summary and output (Part 3/3) |
| 5 | Screenshot_03_Baseline_OK.png | Baseline integrity check — all images OK |
| 6 | Screenshot_04_Tampering_Simulation.png | Insider tampering command executed |
| 7 | Screenshot_05_Tampering_Detected.png | Forensic check detects tampering |
| 8 | Screenshot_06a_Audit_Trail.png | Audit log query — action history |
| 9 | Screenshot_06b_Audit_Trail.png | Audit log query — user attribution |
| 10 | Screenshot_07_Restoration_Verified.png | File restored from backup, integrity verified |
| 11 | Screenshot_08_Git_History.png | Git commit history of the project |

---

## Demonstration Summary

### 1. System Overview (Screenshot 1)
The Digital Medical Imaging Management System running on XAMPP with role-based access control (6 roles).

### 2. Detection Tool (Screenshots 2a, 2b, 2c)
The `scripts/forensic_check.php` script that implements SHA-256 hash verification:
- Reads medical image records from MySQL
- Recomputes SHA-256 hash of each file on disk
- Compares against stored hash
- Reports OK / MISSING / TAMPERED

### 3. Baseline Verification (Screenshot 3)
Initial state:
- 3 medical images verified
- 0 missing files
- 0 tampered files
- Status: All OK

### 4. Tampering Simulation (Screenshot 4)
An insider modifies a stored image file on disk:
- Command: sudo bash -c 'echo "MALICIOUS MODIFICATION" >> IMG-8.png'
- File size and content changed
- Database baseline remains unchanged

### 5. Detection (Screenshot 5)
Forensic check detects the modification:
- IMG-8 flagged as [TAMPERED]
- Stored hash: bc3f249f25ae2fc7f492d8ac11a409605fcc69559d9638a9267b2fd491c948e6
- Current hash: 462d0dbebf721776f5188f27ffdd4b169d3ac267b23269d7648d151c69655857
- Tampered files: 1

### 6. Investigation (Screenshots 6a, 6b)
Audit trail query shows:
- Complete timeline for IMG-8
- User attribution for each action
- Role identification via users.role_id → roles.role_name
- Failed login attempts tracked (3)
- Duplicate upload attempts logged (3)
- IMAGE_INTEGRITY_FAILURE event captured

### 7. Restoration (Screenshot 7)
File restored from backup:
- Before: [TAMPERED]
- After: [OK]
- Baseline integrity re-established

### 8. Version Control (Screenshot 8)
Git commit history showing:
- 10+ commits documenting development
- Forensic script committed
- Documentation tracked
- Reproducible build

---

## Forensic Readiness Evidence

| Capability | Proof |
|-----------|-------|
| Detects tampering | Screenshot 5 |
| Identifies responsible user | Screenshots 6a, 6b |
| Preserves evidence | audit_logs table (73+ entries) |
| Enables recovery | Screenshot 7 |
| Tracks changes | Git history (Screenshot 8) |
| SHA-256 integrity | Screenshots 2a-2c |

---

## Compliance

- ISO/IEC 27037 — Digital evidence handling
- Kenya Data Protection Act (2019)
- Computer Misuse and Cybercrimes Act (2018)

---

DCF-01-0247/2025 — Wairimu David Kariuki
