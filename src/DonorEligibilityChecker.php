<?php

class DonorEligibilityChecker {
    private $age;
    private $weight;
    private $healthConditions;

    public function __construct($age, $weight, $healthConditions) {
        $this->age = $age;
        $this->weight = $weight;
        $this->healthConditions = $healthConditions;
    }

    public function isEligible() {
        return $this->ageIsEligible() && $this->weightIsEligible() && $this->healthConditionsAreEligible();
    }

    private function ageIsEligible() {
        return $this->age >= 18 && $this->age <= 65;
    }

    private function weightIsEligible() {
        return $this->weight >= 50; //minimum weight in kg
    }

    private function healthConditionsAreEligible() {
        // Assuming there's an array of conditions that disqualify a donor
        $disqualifyingConditions = ['HIV', 'Hepatitis B', 'Hepatitis C', 'Syphilis', 'Active Tuberculosis', 'Heart Disease'];

        foreach ($this->healthConditions as $condition) {
            if (in_array($condition, $disqualifyingConditions)) {
                return false;
            }
        }
        return true;
    }
}

// Example usage:
// $checker = new DonorEligibilityChecker(25, 55, ['None']);
// echo $checker->isEligible() ? 'Eligible' : 'Not Eligible';
?>