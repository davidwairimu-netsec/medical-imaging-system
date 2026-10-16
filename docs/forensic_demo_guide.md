# FORENSIC DEMONSTRATION GUIDE
## DCF-01-0247/2025 - Wairimu David Kariuki

---

## 1. Purpose

This guide demonstrates how the Digital Medical Imaging Management System detects, investigates, and provides evidence of a tampering incident.

---

## 2. Scenario

Insider Data Tampering in a Regional Hospital Imaging System.

A radiologist uploads a duplicate scan with a normal result to cover up a medical error, then attempts to delete the original.

---

## 3. Prerequisites

- XAMPP running
- System accessible at http://localhost/System%202/
- MySQL database with sample data
- Logged in as Admin

---

## 4. Demonstration Steps

### Step 1: Baseline Setup

1. Login as admin
2. Create a patient (e.g., PAT-999999)
3. Login as radiologist
4. Upload a scan (IMG-XXXXX) with diagnosis "Pulmonary Embolism"
5. Note the original hash from data_integrity_logs:

    SELECT * FROM data_integrity_logs
    WHERE record_id = 'IMG-XXXXX';

### Step 2: Simulate Tampering

1. As radiologist, upload a duplicate scan with diagnosis "Normal"
2. Note the new hash
3. Delete the original scan (soft delete)

### Step 3: Detection

Query the audit log:

    SELECT * FROM audit_logs
    WHERE record_id = 'IMG-XXXXX'
    ORDER BY created_at;

Expected: Shows upload, upload, and delete actions by the same user.

Query data integrity log:

    SELECT * FROM data_integrity_logs
    WHERE record_id = 'IMG-XXXXX'
    ORDER BY timestamp;

Expected: Shows two different hashes with old and new values.

Query access log:

    SELECT * FROM image_access_logs
    WHERE image_id = 'IMG-XXXXX'
    ORDER BY access_date;

Expected: Shows view and delete actions.

### Step 4: Investigation

Compare hashes:

    Original hash: <hash 1>
    New hash:      <hash 2>

Because they differ, tampering is proven.

The audit_logs table identifies the user responsible:

    SELECT user_id, action, created_at, ip_address
    FROM audit_logs
    WHERE record_id = 'IMG-XXXXX';

### Step 5: Chain of Custody

Evidence preserved:

1. Original image hash in data_integrity_logs
2. Audit trail of all actions
3. Access log with timestamps and IP
4. User attribution
5. No ability to modify log entries

### Step 6: Report

Generate a forensic report including:

- Incident summary
- Evidence collected
- Timeline of events
- User responsible
- Recommendation

---

## 5. Screenshots to Capture

| # | Screenshot | Where |
|---|-----------|-------|
| 1 | Original image upload | image module |
| 2 | audit_logs query result | phpMyAdmin |
| 3 | data_integrity_logs query result | phpMyAdmin |
| 4 | image_access_logs query result | phpMyAdmin |
| 5 | Hash comparison | terminal |
| 6 | Forensic report | docs/ |

---

## 6. Demo Checklist

| # | Step | Status |
|---|------|--------|
| 1 | Login as admin | Pending |
| 2 | Create test patient | Pending |
| 3 | Login as radiologist | Pending |
| 4 | Upload original scan | Pending |
| 5 | Record original hash | Pending |
| 6 | Upload duplicate scan | Pending |
| 7 | Delete original scan | Pending |
| 8 | Query audit_logs | Pending |
| 9 | Query data_integrity_logs | Pending |
| 10 | Query image_access_logs | Pending |
| 11 | Compare hashes | Pending |
| 12 | Generate report | Pending |

---

Student Signature: ______________________________
Date: ______________________________

Supervisor Signature: ______________________________
Date: ______________________________
