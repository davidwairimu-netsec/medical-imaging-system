# MILESTONE 2 - ATTACK / INVESTIGATION SCENARIO
## Digital Medical Imaging Management System
### DCF-01-0247/2025 - Wairimu David Kariuki

---

## 2.1 Scenario Overview

**Scenario Title:** Insider Data Tampering in a Regional Hospital Imaging System

**Case Narrative:**
A radiologist is suspected of covering up a medical error. A CT scan originally showed a pulmonary embolism, but the current record shows a normal result. Management suspects the radiologist altered the record. The DMIMS was implemented with forensic readiness to detect, investigate, and prove such tampering.

---

## 2.2 Threat Model

### 2.2.1 Threat Actors

| Actor | Motivation | Capability | Threat Level |
|-------|-----------|------------|--------------|
| Malicious Insider | Cover up error | Authorized user | High |
| Negligent Insider | Carelessness | Basic user | Medium |
| External Attacker | Data theft | Remote | Medium |
| Compromised Account | Credential theft | Variable | High |

### 2.2.2 Threat Vectors

| # | Vector | Likelihood |
|---|--------|------------|
| 1 | Unauthorized modification | High |
| 2 | Unauthorized deletion | Medium |
| 3 | Duplicate injection | High |
| 4 | Credential sharing | Medium |
| 5 | Session hijacking | Low |
| 6 | SQL injection | Low |

### 2.2.3 CIA Triad Impact

| Principle | Impact | Mitigation |
|-----------|--------|------------|
| Confidentiality | Data exposed | RBAC |
| Integrity | Records altered | SHA-256 |
| Availability | Records unavailable | Backups |

---

## 2.3 Attack Scenario

### Phase 1 - Initial Compromise
Radiologist logs in with valid credentials. Session is logged.

### Phase 2 - Reconnaissance
Insider navigates to images module and searches for target scan.

### Phase 3 - Tampering
Insider uploads duplicate scan with normal results. SHA-256 hash calculated.

### Phase 4 - Duplicate Detection
System detects duplicate. Logs recorded in audit_logs and data_integrity_logs.

### Phase 5 - Attempted Cleanup
Insider deletes original scan. System performs soft delete.

---

## 2.4 Detection Mechanisms

### 2.4.1 SHA-256 Hash Verification
On upload: $hash = hash(chr(39)sha256chr(39), $imageData);

### 2.4.2 Audit Log Analysis
Every action recorded with user_id, action, table_name, record_id, details, ip_address, created_at.

### 2.4.3 Image Access Logs
Tracks view, download, update, delete.

### 2.4.4 Data Integrity Logs
Records old and new hash values with user and timestamp.

---

## 2.5 Investigation Process

### Step 1 - Incident Report
Doctor reports discrepancy to Administrator.

### Step 2 - Forensic Investigation
Query audit_logs, image_access_logs, data_integrity_logs for the affected record.

### Step 3 - Chain of Custody
1. Original hash at upload
2. New hash at upload by radiologist
3. Deleted record
4. Audit log entry
5. Chronological timestamps

### Step 4 - Reporting
Formal forensic report with evidence, analysis, conclusion, recommendation.

---

## 2.6 Forensic Readiness Features (Actual Implementation)

The system implements forensic readiness through a centralized audit trail in the `audit_logs` table.

| Feature | Implementation | Evidence |
|---------|----------------|----------|
| User authentication tracking | logActivity() | `audit_logs.action = 'USER_LOGIN'` |
| Image upload tracking | logActivity() | `audit_logs.action = 'IMAGE_UPLOAD'` |
| Image view tracking | logActivity() | `audit_logs.action = 'IMAGE_VIEW'` |
| Duplicate detection | logActivity() | `audit_logs.action = 'IMAGE_DUPLICATE_ATTEMPT'` |
| File integrity verification | SHA-256 recompute | `scripts/forensic_check.php` |
| User attribution | user_id column | `audit_logs.user_id` |
| Timestamp precision | created_at | `audit_logs.created_at` |
| Chain of custody | Chronological log sequence | `audit_logs` |

### Audit Log Actions Captured

| Action | Trigger |
|--------|---------|
| `USER_LOGIN` | Successful login |
| `USER_LOGOUT` | Logout |
| `IMAGE_UPLOAD` | Successful upload |
| `IMAGE_VIEW` | Image viewed |
| `IMAGE_DUPLICATE_ATTEMPT` | Duplicate blocked |
| `IMAGE_DELETE` | Image deleted |
| `USER_CREATE` | New user (admin) |
| `USER_UPDATE` | User updated (admin) |

### Evidence Query

To investigate a tampering incident:

```sql
SELECT log_id, user_id, action, entity_type, entity_id, created_at 
FROM audit_logs 
WHERE entity_id = <image_id>
ORDER BY created_at;
## 2.6 Forensic Readiness Features

| Feature | Implementation | Table |
|---------|----------------|-------|
| Audit trail | logActivity | audit_logs |
| Access tracking | logImageAccess | image_access_logs |
| Integrity verification | SHA-256 | data_integrity_logs |
| User attribution | user_id | All tables |
| Timestamp | CURRENT_TIMESTAMP | All tables |
| IP capture | REMOTE_ADDR | audit_logs |

---

## 2.7 Mapping to Form 2B Milestone 2

| Requirement | Evidence |
|-------------|----------|
| Secure test environment | Isolated local lab |
| OS configuration | PHP 8.2, Apache, DB |
| Attack scenario | Insider tampering |
| Logging and monitoring | Log tables |
| Environment documentation | This document |

---

## 2.8 Conclusion

The DMIMS is forensically ready. It detects insider threats, preserves evidence, establishes chain of custody, and supports legal action.

---

Student Signature: ______________________________
Date: ______________________________

Supervisor Signature: ______________________________
Date: ______________________________
