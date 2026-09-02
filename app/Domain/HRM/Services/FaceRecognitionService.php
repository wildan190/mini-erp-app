<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class FaceRecognitionService
{
    /**
     * Enroll employee face by storing the image.
     * In production, this would also generate face encoding using ML library.
     *
     * @param Employee $employee
     * @param UploadedFile $faceImage
     * @return array
     */
    /**
     * Enroll employee face by storing the image.
     */
    public function enrollFace(Employee $employee, UploadedFile $faceImage): array
    {
        $faceApiUrl = env('FACE_API_URL', 'http://face-api:5000');

        // Try HTTP API microservice first
        try {
            $imageContent = @file_get_contents($faceImage->getRealPath()) ?: 'dummy_image_data';
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->attach('image', $imageContent, $faceImage->getClientOriginalName())
                ->post("{$faceApiUrl}/encode");

            if ($response->successful() && isset($response->json()['encoding'])) {
                $output = $response->json();
                $faceEncoding = json_encode($output['encoding']);

                $path = $faceImage->store('faces/enrolled', 'public');
                $employee->update([
                    'face_image_path' => $path,
                    'face_encoding' => $faceEncoding,
                    'requires_face_verification' => true,
                ]);

                return [
                    'success' => true,
                    'message' => 'Face enrolled successfully',
                    'path' => $path,
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info("HTTP Face API unavailable, falling back to local Python CLI: " . $e->getMessage());
        }

        // Fallback to local python script execution
        $tempPath = $faceImage->getRealPath();
        $pythonCommand = config('services.python.executable');
        $process = new Process([$pythonCommand, base_path('scripts/face_rec.py'), 'enroll', $tempPath]);
        $process->run();

        $output = json_decode($process->getOutput(), true);

        if (!$process->isSuccessful() || !$output || !isset($output['success']) || !$output['success']) {
            $errorMessage = $output['message'] ?? 'Failed to extract face encoding';
            $errorOutput = $process->getErrorOutput();
            
            \Illuminate\Support\Facades\Log::error('Face Recognition Error', [
                'message' => $errorMessage,
                'error_output' => $errorOutput,
                'command' => $process->getCommandLine()
            ]);

            return [
                'success' => false,
                'message' => $errorMessage . ($errorOutput ? ": " . $errorOutput : ""),
            ];
        }

        $faceEncoding = json_encode($output['encoding']);
        $path = $faceImage->store('faces/enrolled', 'public');

        $employee->update([
            'face_image_path' => $path,
            'face_encoding' => $faceEncoding,
            'requires_face_verification' => true,
        ]);

        return [
            'success' => true,
            'message' => 'Face enrolled successfully',
            'path' => $path,
        ];
    }

    /**
     * Verify if uploaded face matches employee's enrolled face.
     */
    public function verifyFace(Employee $employee, UploadedFile $faceImage): array
    {
        if (!$employee->face_encoding) {
            return [
                'verified' => false,
                'confidence' => 0,
                'message' => 'No enrolled face found for employee',
            ];
        }

        $faceApiUrl = env('FACE_API_URL', 'http://face-api:5000');

        // Try HTTP API microservice first
        try {
            $imageContent = @file_get_contents($faceImage->getRealPath()) ?: 'dummy_image_data';
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->attach('image', $imageContent, $faceImage->getClientOriginalName())
                ->post("{$faceApiUrl}/verify", [
                    'target_encoding' => $employee->face_encoding,
                    'tolerance' => 0.5,
                ]);

            if ($response->successful()) {
                $output = $response->json();
                return [
                    'verified' => $output['verified'] ?? false,
                    'confidence' => 1 - ($output['distance'] ?? 0),
                    'message' => $output['message'] ?? 'Face verification completed',
                ];
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info("HTTP Face API unavailable, falling back to local Python CLI: " . $e->getMessage());
        }

        // Fallback to local python script execution
        $tempPath = $faceImage->getRealPath();
        $pythonCommand = config('services.python.executable');
        $process = new Process([$pythonCommand, base_path('scripts/face_rec.py'), 'verify', $tempPath, $employee->face_encoding]);
        $process->run();

        $output = json_decode($process->getOutput(), true);

        if (!$process->isSuccessful() || !$output) {
            $errorOutput = $process->getErrorOutput();
            \Illuminate\Support\Facades\Log::error('Face Verification Error', [
                'raw_output' => $process->getOutput(),
                'error_output' => $errorOutput,
                'command' => $process->getCommandLine()
            ]);

            return [
                'verified' => false,
                'confidence' => 0,
                'message' => 'Verification process failed' . ($errorOutput ? ": " . $errorOutput : ""),
            ];
        }

        return [
            'verified' => $output['verified'] ?? false,
            'confidence' => $output['confidence'] ?? 0,
            'message' => $output['message'] ?? 'Face verification completed',
        ];
    }

    /**
     * Remove employee's face data.
     *
     * @param Employee $employee
     * @return bool
     */
    public function removeFaceData(Employee $employee): bool
    {
        if ($employee->face_image_path) {
            Storage::disk('public')->delete($employee->face_image_path);
        }

        $employee->update([
            'face_image_path' => null,
            'face_encoding' => null,
            'requires_face_verification' => false,
        ]);

        return true;
    }

    /**
     * Store attendance face image for audit purposes.
     *
     * @param UploadedFile $faceImage
     * @return string
     */
    public function storeAttendanceFaceImage(UploadedFile $faceImage): string
    {
        return $faceImage->store('faces/attendance', 'public');
    }

}
