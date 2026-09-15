<?php
/**
 * Application Constants
 */

// User roles
define('ROLE_ADMIN', 'admin');
define('ROLE_RADIOLOGIST', 'radiologist');
define('ROLE_TECHNICIAN', 'technician');
define('ROLE_DOCTOR', 'doctor');
define('ROLE_RECORDS', 'records');

// Account statuses
define('STATUS_ACTIVE', 'active');
define('STATUS_DISABLED', 'disabled');
define('STATUS_LOCKED', 'locked');

// Record statuses
define('RECORD_ACTIVE', 'active');
define('RECORD_ARCHIVED', 'archived');

// Imaging types
define('IMAGING_TYPES', ['X-ray', 'CT', 'MRI', 'Ultrasound']);

// Audit actions
define('AUDIT_LOGIN', 'USER_LOGIN');
define('AUDIT_LOGOUT', 'USER_LOGOUT');
define('AUDIT_LOGIN_FAILED', 'USER_LOGIN_FAILED');
define('AUDIT_PATIENT_CREATE', 'PATIENT_CREATE');
define('AUDIT_PATIENT_UPDATE', 'PATIENT_UPDATE');
define('AUDIT_PATIENT_ARCHIVE', 'PATIENT_ARCHIVE');
define('AUDIT_IMAGE_UPLOAD', 'IMAGE_UPLOAD');
define('AUDIT_IMAGE_DUPLICATE', 'IMAGE_DUPLICATE_ATTEMPT');
define('AUDIT_IMAGE_VIEW', 'IMAGE_VIEW');
define('AUDIT_IMAGE_DOWNLOAD', 'IMAGE_DOWNLOAD');
define('AUDIT_IMAGE_UPDATE', 'IMAGE_UPDATE');
define('AUDIT_IMAGE_ARCHIVE', 'IMAGE_ARCHIVE');
define('AUDIT_USER_CREATE', 'USER_CREATE');
define('AUDIT_USER_UPDATE', 'USER_UPDATE');
define('AUDIT_USER_DISABLE', 'USER_DISABLE');
define('AUDIT_BACKUP_CREATE', 'BACKUP_CREATE');
// Head Nurse role
define('ROLE_HEAD_NURSE', 'head_nurse');

// Nurse availability statuses
define('NURSE_AVAILABLE', 'available');
define('NURSE_BUSY', 'busy');
define('NURSE_OFF_DUTY', 'off_duty');
define('NURSE_ON_LEAVE', 'on_leave');

// Assignment statuses
define('ASSIGNMENT_ACTIVE', 'active');
define('ASSIGNMENT_ENDED', 'ended');
