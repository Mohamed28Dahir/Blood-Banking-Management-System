<?php

class AppointmentScheduler {
    protected $appointments = [];

    // Method for booking an appointment
    public function bookAppointment($dateTime, $patientName) {
        $this->appointments[] = [
            'dateTime' => $dateTime,
            'patientName' => $patientName,
            'status' => 'booked'
        ];
        return "Appointment booked for $patientName on $dateTime.";
    }

    // Method for canceling an appointment
    public function cancelAppointment($index) {
        if (isset($this->appointments[$index])) {
            $this->appointments[$index]['status'] = 'canceled';
            return "Appointment canceled.";
        }
        return "Appointment not found.";
    }

    // Method for rescheduling an appointment
    public function rescheduleAppointment($index, $newDateTime) {
        if (isset($this->appointments[$index])) {
            $this->appointments[$index]['dateTime'] = $newDateTime;
            return "Appointment rescheduled to $newDateTime.";
        }
        return "Appointment not found.";
    }

    // Method for sending reminders
    public function sendReminder($index) {
        if (isset($this->appointments[$index])) {
            return "Reminder sent for appointment on " . $this->appointments[$index]['dateTime'] . " to " . $this->appointments[$index]['patientName'] . '.';
        }
        return "Appointment not found.";
    }
}
?>