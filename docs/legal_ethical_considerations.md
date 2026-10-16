# CHAPTER 1.7 - LEGAL AND ETHICAL CONSIDERATIONS
## Digital Medical Imaging Management System
### DCF-01-0247/2025 - Wairimu David Kariuki

---

## 1.7.1 Scope Boundaries

### In-Scope
The Digital Medical Imaging Management System (DMIMS) covers:
- Patient Registration
- Medical Image Management (X-ray, CT, MRI, Ultrasound, DICOM)
- Duplicate Detection using SHA-256 hash verification
- User Authentication and Role-Based Access Control
- Image Requests and approvals
- Audit Logging of all actions
- PDF Reporting
- Data Integrity Controls
- Backup Management

### Out-of-Scope
The system does NOT cover:
- Pharmacy management
- Billing and financial management
- Laboratory information systems
- Inpatient administration
- Human resource management

### Regulatory Boundary
Operates strictly within the Republic of Kenya.

---

## 1.7.2 Authorization Requirements

| Role | Authorization Level | Justification |
|------|--------------------|--------------|
| Administrator | Full system access | System governance |
| Radiologist / Technician | Create, modify, view images | Operates imaging equipment |
| Doctor | Read-only patient imaging | Clinical decisions |
| Head Nurse | Ward-level registration and requests | Ward coordination |
| Nurse | Routine registration and viewing | Frontline care |
| Patient Portal | View own records only | Data subject right |

### Authorization Principles
1. Least Privilege Principle
2. Need-to-Know Basis
3. Mandatory Authentication
4. Session-Based Access (30-minute timeout)
5. Role-Based Access Control
6. Audit Trail Requirement

Authorization is enforced server-side via the Auth class in the authentication module.

---

## 1.7.3 Legal Compliance

### 1.7.3.1 Kenya Data Protection Act (2019)

| Requirement | Compliance Measure |
|-------------|-------------------|
| Lawful processing | Only authorized personnel |
| Purpose limitation | Medical imaging only |
| Data minimization | Only necessary data |
| Accuracy | Duplicate detection |
| Storage limitation | Per hospital policy |
| Security safeguards | Password hashing, RBAC, CSRF |
| Data subject rights | Patient Portal |
| Breach notification | Audit logs |

### 1.7.3.2 Computer Misuse and Cybercrimes Act (2018)

| Provision | Implementation |
|-----------|----------------|
| Unauthorized access (S.4) | Strong authentication |
| Unauthorized interference (S.5) | Input validation |
| Unauthorized interception (S.7) | Secure sessions |
| Data espionage (S.8) | RBAC and audit logs |
| Cyber harassment (S.24) | No public exposure |
| Forensics readiness (S.61) | Comprehensive audit trail |

### 1.7.3.3 ISO/IEC 27037 - Digital Evidence Standards

| ISO 27037 Requirement | Implementation |
|----------------------|----------------|
| Identification | Unique IDs in audit_logs |
| Collection | Automatic logging |
| Acquisition | Immutable records |
| Preservation | Append-only logs |
| Chain of Custody | Hash + logs + timestamps |
| Documentation | Complete trail |

### 1.7.3.4 Other Applicable Laws
- Health Act (2017)
- Public Health Act (Cap 242)
- Access to Information Act (2016)
- Medical Practitioners and Dentists Act (Cap 253)

---

## 1.7.4 Ethical Safeguards

### 1.7.4.1 Patient Consent
Informed consent is captured at registration.

### 1.7.4.2 Patient Confidentiality
All access is logged and attributable to a specific user.

### 1.7.4.3 Duty of Care
Accurate records, integrity verification, backups.

### 1.7.4.4 Professional Ethics
Adherence to Kenya ICT Authority, ISACA, and ACM codes.

### 1.7.4.5 Research Ethics
No real patient data used. Synthetic test data only. Approval from Zetech University.

---

## 1.7.5 Ethical and Legal Boundaries

### System Will NOT:
1. Share data with unauthorized parties
2. Use data commercially
3. Bypass consent
4. Store data outside Kenya
5. Allow untraceable access
6. Modify records without audit entry

### System WILL:
1. Log every action
2. Verify identity on login
3. Restrict by role and department
4. Preserve integrity via hashing
5. Provide patient access
6. Comply with Kenyan law

---

## 1.7.6 Declaration

I, WAIRIMU DAVID KARIUKI (DCF-01-0247/2025), declare that this system has been developed with full consideration of legal and ethical requirements.

Student Signature: ______________________________
Date: ______________________________

Supervisor Signature: ______________________________
Date: ______________________________
