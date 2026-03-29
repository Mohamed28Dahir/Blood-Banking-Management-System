<?php

class AppointmentScheduler {
    private $timeSlots = [];
    private $appointments = [];

    public function __construct() {
        // Initialize available time slots for the appointment
        $this->initializeTimeSlots();
    }

    private function initializeTimeSlots() {
        // Define time slots for appointments
        for ($hour = 9; $hour <= 17; $hour++) {
            $this->timeSlots[] = sprintf('%02d:00', $hour);
            $this->timeSlots[] = sprintf('%02d:30', $hour);
        }
    }

    public function bookAppointment($timeSlot, $donorId) {
        if (!$this->isTimeSlotAvailable($timeSlot)) {
            return 'Time slot is not available.';
        }
        if (!$this->checkDonorEligibility($donorId)) {
            return 'Donor not eligible for appointment.';
        }

        // Book the appointment
        $this->appointments[] = ['timeSlot' => $timeSlot, 'donorId' => $donorId];
        $this->removeTimeSlot($timeSlot);
        return 'Appointment booked successfully.';
    }

    private function isTimeSlotAvailable($timeSlot) {
        return in_array($timeSlot, $this->timeSlots);
    }

    private function removeTimeSlot($timeSlot) {
        $key = array_search($timeSlot, $this->timeSlots);
        if ($key !== false) {
            unset($this->timeSlots[$key]);
        }
    }

    private function checkDonorEligibility($donorId) {
        // Implement eligibility check logic (This is a placeholder)
        return true;
    }

    public function sendReminder($appointmentId) {
        // Logic to send reminder to the donor
        return 'Reminder sent to donor.';
    }

    public function manageCancellation($appointmentId) {
        // Logic to manage appointment cancellation
        return 'Appointment cancelled.';
    }
}

?>