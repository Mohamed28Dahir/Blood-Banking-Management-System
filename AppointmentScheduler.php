<?php

class AppointmentScheduler {
    private $appointments = [];
    private $availableSlots = [];

    public function __construct() {
        // Initialize available slots. For simplicity, let's assume slots are in hours from 9 to 17.
        for ($hour = 9; $hour <= 17; $hour++) {
            $this->availableSlots[] = "{$hour}:00";
        }
    }

    public function bookAppointment($date, $time) {
        $slot = "$date $time";
        if (in_array($time, $this->availableSlots)) {
            $this->appointments[$slot] = true;
            $this->removeSlot($time);
            return "Appointment booked for $slot.";
        }
        return "Slot not available for booking.";
    }

    public function cancelAppointment($date, $time) {
        $slot = "$date $time";
        if (isset($this->appointments[$slot])) {
            unset($this->appointments[$slot]);
            $this->availableSlots[] = $time;
            return "Appointment on $slot canceled.";
        }
        return "No appointment found for $slot.";
    }

    public function getAvailableSlots() {
        return $this->availableSlots;
    }

    public function sendReminder($date, $time) {
        $slot = "$date $time";
        if (isset($this->appointments[$slot])) {
            return "Reminder: You have an appointment on $slot.";
        }
        return "No upcoming appointment for $slot.";
    }

    private function removeSlot($time) {
        if (($key = array_search($time, $this->availableSlots)) !== false) {
            unset($this->availableSlots[$key]);
        }
    }
}

?>