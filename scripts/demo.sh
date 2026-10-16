#!/bin/bash
cd "/opt/lampp/htdocs/System 2"

echo "════════════════════════════════════════════════"
echo "  MILESTONE 3 DEMONSTRATION"
echo "  Forensic Integrity Check"
echo "  DCF-01-0247/2025 - Wairimu David Kariuki"
echo "════════════════════════════════════════════════"
echo ""

echo "▶ STEP 1: BASELINE VERIFICATION"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php

echo ""
read -p "Press ENTER to simulate tampering..."

echo ""
echo "▶ STEP 2: SIMULATE INSIDER TAMPERING"
echo "────────────────────────────────────────────────"
sudo bash -c 'echo "INSIDER ATTACK - $(date)" >> "uploads/medical_images/d47be4ddccf13552e588e8e44eb06e49.png"'
echo "✅ File modified on disk"

echo ""
read -p "Press ENTER to detect tampering..."

echo ""
echo "▶ STEP 3: DETECTION"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php

echo ""
read -p "Press ENTER to view audit trail..."

echo ""
echo "▶ STEP 4: AUDIT TRAIL"
echo "────────────────────────────────────────────────"
/opt/lampp/bin/mysql -u root medical_imaging_db -e "
SELECT log_id, user_id, action, entity_type, entity_id, created_at
FROM audit_logs
WHERE entity_id = 8
ORDER BY log_id DESC LIMIT 5;
"

echo ""
read -p "Press ENTER to restore the file..."

echo ""
echo "▶ STEP 5: RESTORE FILE"
echo "────────────────────────────────────────────────"
sudo sed -i '$ d' "uploads/medical_images/d47be4ddccf13552e588e8e44eb06e49.png"
echo "✅ File restored"

echo ""
read -p "Press ENTER to verify restoration..."

echo ""
echo "▶ STEP 6: VERIFY"
echo "────────────────────────────────────────────────"
php scripts/forensic_check.php

echo ""
echo "════════════════════════════════════════════════"
echo "  DEMO COMPLETE"
echo "════════════════════════════════════════════════"
