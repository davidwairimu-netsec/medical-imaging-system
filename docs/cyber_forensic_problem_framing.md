# CHAPTER 1.2 - CYBER / FORENSIC PROBLEM FRAMING
## Digital Medical Imaging Management System
### DCF-01-0247/2025 - Wairimu David Kariuki

---

## 1.2.1 Introduction

This section reframes the problem statement from a pure IT perspective to a Cyber Security and Forensics (DCF) perspective, emphasizing:
1. Data Integrity Failures
2. Unauthorized Modification
3. Forensic Non-Readiness

---

## 1.2.2 Data Integrity Failures

### Problem
Digital imaging systems suffer from data integrity failures - inconsistent, incomplete, or unreliable records.

### Evidence
- Duplicate files with conflicting results
- Inconsistent metadata
- Missing scans
- Silent corruption

### Impact
| Area | Consequence |
|------|-------------|
| Clinical | Misdiagnosis, repeated scans |
| Financial | Higher operational costs |
| Legal | Non-compliance with DPA 2019 |
| Trust | Loss of confidence |
| Forensic | No evidence for accountability |

### Forensic Dimension
No integrity verification means tampering cannot be detected - a violation of ISO/IEC 27037.

---

## 1.2.3 Unauthorized Modification

### Problem
Weak access controls and no audit trails allow insiders to modify or delete scans without evidence.

### Evidence
- Disappearing scans
- Metadata changes without logs
- Credential sharing
- No role restrictions
- No cryptographic proof

### Impact
| Area | Consequence |
|------|-------------|
| Safety | Incorrect diagnosis |
| Accountability | Untraceable actions |
| Legal | CMCA 2018 violation |
| Compliance | DPA 2019 failure |
| Evidence | Tampering unprovable |

### Forensic Dimension
No immutable audit trail or hash verification means modifications cannot be detected or attributed.

---

## 1.2.4 Forensic Non-Readiness

### Problem
Systems cannot detect tampering, attribute actions, preserve evidence legally, reconstruct timelines, or support investigations.

### Evidence
- No centralized logs
- No user attribution
- No cryptographic hashing
- No access logging
- No backup verification
- No incident response

### Impact
| Area | Consequence |
|------|-------------|
| Investigation | Cannot conduct |
| Legal | Evidence inadmissible |
| Regulatory | ISO 27037 non-compliance |
| Reputation | Cannot defend claims |
| Accountability | No responsibility |

### Forensic Dimension
Violates core ISO/IEC 27037 principles.

---

## 1.2.5 Reframed Problem Statement

The regional hospital digital imaging system suffers from three critical cybersecurity and forensic deficiencies: (1) data integrity failures that compromise accuracy and reliability; (2) unauthorized modification of patient scans that cannot be detected or attributed; and (3) forensic non-readiness that prevents investigation, proof of tampering, or legally admissible evidence preservation. These deficiencies violate the Kenya Data Protection Act (2019), the Computer Misuse and Cybercrimes Act (2018), and ISO/IEC 27037 digital evidence standards, exposing the hospital to clinical, legal, financial, and reputational risk.

---

## 1.2.6 Reframed Justification

This research addresses cybersecurity and forensic readiness gaps in regional hospital imaging systems. Through SHA-256 hash-based integrity verification, comprehensive audit logging, RBAC, and chain-of-custody mechanisms, the system:

1. Preserves data integrity (ISO/IEC 27037)
2. Prevents and detects unauthorized modification (CMCA 2018)
3. Enables forensic investigations
4. Protects patient data (DPA 2019)
5. Provides legally admissible evidence

---

## 1.2.7 Reframed Objectives

1. Investigate cybersecurity and forensic readiness gaps
2. Identify integrity, modification, and non-readiness challenges
3. Design a forensically-ready DMIMS
4. Implement SHA-256 duplicate detection and integrity monitoring
5. Implement comprehensive audit logging and chain of custody (ISO 27037)
6. Assess effectiveness in detecting tampering and preserving evidence

---

## 1.2.8 Reframed Research Questions

1. What integrity, modification, and forensic gaps exist?
2. How can SHA-256 detect duplicates and tampering?
3. How can audit logging establish chain of custody?
4. How can RBAC prevent unauthorized modification?
5. To what extent can the system support forensic investigations?
6. How does the system comply with Kenyan law and ISO 27037?

---

## 1.2.9 IT vs DCF Framing

| Dimension | IT-Focused | DCF-Focused |
|-----------|-----------|-------------|
| Problem | Missing records | Integrity, modification, non-readiness |
| Objective | Build a system | Build a forensically-ready system |
| Security | Basic auth | RBAC + integrity + audit |
| Evidence | N/A | Hash, chain of custody |
| Compliance | N/A | DPA 2019, CMCA 2018, ISO 27037 |
| Deliverable | Working system | Working + forensic readiness |

---

## 1.2.10 Conclusion

The reframed problem aligns the project with the Diploma in Cyber Security and Forensics (DCF) and satisfies Form 2B Milestone 1.

---

Student Signature: ______________________________
Date: ______________________________

Supervisor Signature: ______________________________
Date: ______________________________
