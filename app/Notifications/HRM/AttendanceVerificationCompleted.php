<?php

namespace App\Notifications\HRM;

use App\Models\HRM\Attendance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AttendanceVerificationCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Attendance $attendance,
        public bool $faceVerified,
        public bool $locationVerified,
        public string $faceMessage = '',
        public string $locationMessage = ''
    ) {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $status = ($this->faceVerified && $this->locationVerified) ? 'Verified' : 'Flagged';
        $greeting = ($this->faceVerified && $this->locationVerified)
            ? 'Attendance Verified Successfully!'
            : 'Attendance Verification Issue';

        $mail = (new MailMessage)
            ->subject("Attendance Verification: $status")
            ->greeting($greeting);

        if ($this->faceVerified && $this->locationVerified) {
            $mail->line('Your attendance has been successfully verified.');
        } else {
            $mail->line('There was an issue with your attendance verification:');

            if (!$this->faceVerified) {
                $mail->line("• Face Verification: {$this->faceMessage}");
            }

            if (!$this->locationVerified) {
                $mail->line("• Location Verification: {$this->locationMessage}");
            }

            $mail->line('Please contact HR if you believe this is an error.');
        }

        return $mail->line('Check-in Time: ' . $this->attendance->clock_in->format('H:i'))
            ->action('View Attendance', url('/hrm/attendance'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'attendance_id' => $this->attendance->id,
            'date' => $this->attendance->date,
            'clock_in' => $this->attendance->clock_in,
            'face_verified' => $this->faceVerified,
            'location_verified' => $this->locationVerified,
            'face_message' => $this->faceMessage,
            'location_message' => $this->locationMessage,
            'status' => $this->attendance->status,
        ];
    }
}
