<?php

class BloodExpiryTracker {
    private $bloodBags = [];

    public function addBloodBag($id, $expiryDate) {
        $this->bloodBags[$id] = $expiryDate;
    }

    public function markAsExpired($id) {
        if (isset($this->bloodBags[$id])) {
            unset($this->bloodBags[$id]);
            return true;
        }
        return false;
    }

    public function calculateWastage() {
        $currentDate = new DateTime('2026-03-29 17:11:29');
        $expiredCount = 0;

        foreach ($this->bloodBags as $expiryDate) {
            if (new DateTime($expiryDate) < $currentDate) {
                $expiredCount++;
            }
        }

        return $expiredCount;
    }
}

?>