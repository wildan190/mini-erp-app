<?php

namespace App\Jobs\HRM;

use App\Models\HRM\Attendance;
use App\Models\HRM\Employee;
use App\Models\HRM\OfficeLocation;
use App\Notifications\HRM\AttendanceVerificationCompleted;
use App\Services\HRM\FaceRecognitionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessAttendanceVerification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Attendance $attendance,
        public ?string $faceImagePath = null,
        public ?int $officeLocationId = null,
        public ?float $latitude = null,
        public ?float $longitude = null,
        public string $verificationType = 'clock_in'
    ) {
    }

    /**
     * Execute the job.
     */
    public function handle(FaceRecognitionService $faceRecognitionService): void
    {
        try {
            $employee = $this->attendance->employee;
            $faceVerified = true;
            $locationVerified = true;
            $faceMessage = '';
            $locationMessage = '';

            // Face Verification
            if ($this->faceImagePath && $employee->requires_face_verification) {
                $fullPath = Storage::disk('public')->path($this->faceImagePath);

                if (file_exists($fullPath)) {
                    $faceImage = new \Illuminate\Http\UploadedFile(
                        $fullPath,
                        basename($this->faceImagePath),
                        mime_content_type($fullPath),
                        null,
                        true
                    );

                    $verificationResult = $faceRecognitionService->verifyFace($employee, $faceImage);
                    $faceVerified = $verificationResult['verified'];
                    $faceMessage = $verificationResult['message'];

                    // Store permanent attendance face image
                    if ($faceVerified) {
                        $storedPath = $faceRecognitionService->storeAttendanceFaceImage($faceImage);
                        $this->attendance->update(['face_image_path' => $storedPath]);
                    }
                } else {
                    $faceVerified = false;
                    $faceMessage = 'Face image file not found';
                }
            }

            // Location Verification
            if ($this->officeLocationId && $this->latitude && $this->longitude) {
                $officeLocation = OfficeLocation::find($this->officeLocationId);

                if ($officeLocation) {
                    $locationVerified = $officeLocation->isWithinRadius($this->latitude, $this->longitude);

                    if (!$locationVerified) {
                        $distance = $officeLocation->getDistanceFrom($this->latitude, $this->longitude);
                        $locationMessage = "Outside office radius. Distance: " . round($distance) . " meters.";
                    } else {
                        $locationMessage = "Within office radius";
                    }
                }
            }

            // Update attendance with verification results
            $updateData = [
                'face_verification_status' => $this->faceImagePath
                    ? ($faceVerified ? 'verified' : 'failed')
                    : 'skipped',
                'location_verification_status' => $this->officeLocationId
                    ? ($locationVerified ? 'within_radius' : 'outside_radius')
                    : 'skipped',
            ];

            // If both verifications failed, mark attendance as flagged
            if (!$faceVerified || !$locationVerified) {
                $updateData['status'] = 'flagged';
                $updateData['notes'] = ($this->attendance->notes ?? '') .
                    "\nVerification Issues: " .
                    ($faceVerified ? '' : "Face: $faceMessage. ") .
                    ($locationVerified ? '' : "Location: $locationMessage.");
            }

            $this->attendance->update($updateData);

            // Send notification to employee
            if ($employee->user) {
                $employee->user->notify(new AttendanceVerificationCompleted(
                    $this->attendance,
                    $faceVerified,
                    $locationVerified,
                    $faceMessage,
                    $locationMessage
                ));
            }

            // Clean up temporary face image if exists
            if ($this->faceImagePath && Storage::disk('public')->exists($this->faceImagePath)) {
                Storage::disk('public')->delete($this->faceImagePath);
            }

        } catch (\Exception $e) {
            Log::error('Attendance verification failed', [
                'attendance_id' => $this->attendance->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            $this->attendance->update([
                'face_verification_status' => 'error',
                'location_verification_status' => 'error',
                'status' => 'flagged',
                'notes' => ($this->attendance->notes ?? '') . "\nVerification Error: " . $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Attendance verification job failed after all retries', [
            'attendance_id' => $this->attendance->id,
            'error' => $exception->getMessage(),
        ]);

        $this->attendance->update([
            'face_verification_status' => 'failed',
            'location_verification_status' => 'failed',
            'status' => 'flagged',
            'notes' => ($this->attendance->notes ?? '') . "\nVerification failed after retries: " . $exception->getMessage(),
        ]);
    }
}
