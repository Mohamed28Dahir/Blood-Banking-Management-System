<?php

class BloodExpiryTracker {
    private $bloodUnits = [];

    // Add blood unit with expiry date
    public function addBloodUnit($unitId, $expiryDate) {
        $this->bloodUnits[$unitId] = $expiryDate;
    }

    // Check for expiring blood units
    public function checkExpiringBloodUnits($currentDate) {
        $expiringUnits = [];
        foreach ($this->bloodUnits as $unitId => $expiryDate) {
            if ($expiryDate <= $currentDate) {
                $expiringUnits[] = $unitId;
            }
        }
        return $expiringUnits;
    }

    // Calculate wastage based on expiring units
    public function calculateWastage($currentDate) {
        $wastageCount = 0;
        foreach ($this->bloodUnits as $expiryDate) {
            if ($expiryDate <= $currentDate) {
                $wastageCount++;
            }
        }
        return $wastageCount;
    }

    // Send alerts for expiring blood units
    public function sendAlerts($currentDate) {
        $expiringUnits = $this->checkExpiringBloodUnits($currentDate);
        if (!empty($expiringUnits)) {
            // Logic to send alert (e.g., email, SMS)
            foreach ($expiringUnits as $unitId) {
                echo "Alert: Blood unit {$unitId} is expiring!\n";
            }
        } else {
            echo "No expiring blood units.\n";
        }
    }
}

?>