<?php

class DonorEligibilityChecker {
    const MIN_AGE = 18;
    const MAX_AGE = 65;
    const MIN_WEIGHT = 50; // in kg
    const DONATION_INTERVAL = 56; // in days

    private $lastDonationDate;
    private $age;
    private $weight;

    public function __construct($age, $weight, $lastDonationDate) {
        $this->age = $age;
        $this->weight = $weight;
        $this->lastDonationDate = new DateTime($lastDonationDate);
    }

    public function isEligible() {
        return $this->isAgeEligible() && $this->isWeightEligible() && $this->isDonationIntervalEligible();
    }

    private function isAgeEligible() {
        return $this->age >= self::MIN_AGE && $this->age <= self::MAX_AGE;
    }

    private function isWeightEligible() {
        return $this->weight >= self::MIN_WEIGHT;
    }

    private function isDonationIntervalEligible() {
        $currentDate = new DateTime();
        $interval = $currentDate->diff($this->lastDonationDate)->days;
        return $interval >= self::DONATION_INTERVAL;
    }
}

// Example usage:
//$checker = new DonorEligibilityChecker(25, 60, '2026-01-01');
//echo $checker->isEligible() ? 'Eligible' : 'Not Eligible';
?>