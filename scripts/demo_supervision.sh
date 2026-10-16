#!/bin/bash
#
# Milestone 3 - Live Supervision Demo
# DCF-01-0247/2025 - Wairimu David Kariuki
#

cd "/opt/lampp/htdocs/System 2" || exit 1

TARGET_FILE="uploads/medical_images/d47be4ddccf13552e588e8e44eb06e49.png"
BACKUP_FILE="/tmp/img8_backup.png"

echo "════════════════════════════════════════════════"
echo "  MILESTONE 3 — LIVE DEMONSTRATION"
echo "  Forensic Integrity Detection"
echo "  DCF-01-0247/2025 - Wairimu David Kariuki"
echo "════════════════════════════════════════════════"
echo ""

echo "▶ STEP 1: BASELINE — Verify all images are intact"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php
echo ""
read -p "Press ENTER to continue to tampering simulation..."

echo ""
echo "▶ STEP 2: SIMULATE INSIDER TAMPERING"
echo "────────────────────────────────────────────────"
echo "A malicious insider modifies a stored scan on disk."
echo "Command: echo 'MALICIOUS MODIFICATION' >> IMG-8.png"
echo ""
sudo bash -c "echo 'INSIDER TAMPERING - $(date)' >> '$TARGET_FILE'"
echo "✅ File modified on disk"
echo ""

echo "Current file details:"
ls -l "$TARGET_FILE"
echo ""

read -p "Press ENTER to run detection..."

echo ""
echo "▶ STEP 3: DETECTION — Forensic check detects tampering"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php
echo ""

read -p "Press ENTER to view audit trail..."

echo ""
echo "▶ STEP 4: AUDIT TRAIL — Chain of custody"
echo "────────────────────────────────────────────────"
echo "Who did what and when?"
echo ""
/opt/lampp/bin/mysql -u root medical_imaging_db -e "
SELECT 
    a.log_id, 
    a.action, 
    a.entity_type,
    a.entity_id, 
    u.username, 
    u.full_name,
    DATE_FORMAT(a.created_at, '%Y-%m-%d %H:%i:%s') as timestamp
FROM audit_logs a
LEFT JOIN users u ON a.user_id = u.user_id
WHERE a.entity_id = 8
ORDER BY a.log_id;
"
echo ""

read -p "Press ENTER to restore the file from backup..."

echo ""
echo "▶ STEP 5: RESTORATION — Return to clean state"
echo "────────────────────────────────────────────────"
if [ -f "$BACKUP_FILE" ]; then
    sudo cp "$BACKUP_FILE" "$TARGET_FILE"
    echo "✅ File restored from backup"
else
    echo "❌ Backup not found at $BACKUP_FILE"
    echo "   Cannot restore automatically."
    exit 1
fi
echo ""

read -p "Press ENTER to verify restoration..."

echo ""
echo "▶ STEP 6: VERIFICATION — Integrity restored"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php

echo ""
echo "════════════════════════════════════════════════"
echo "  DEMONSTRATION COMPLETE"
echo "  Tampering detected and recovery verified"
echo "════════════════════════════════════════════════"
