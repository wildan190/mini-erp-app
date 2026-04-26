<?php

namespace App\Services\HRM;

use App\Models\HRM\Employee;
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
    public function enrollFace(Employee $employee, UploadedFile $faceImage): array
    {
        // Store the face image temporarily for processing
        $tempPath = $faceImage->getRealPath();

        // Execute python script to enroll face
        $pythonCommand = config('services.python.executable');
        $process = new Process([$pythonCommand, base_path('scripts/face_rec.py'), 'enroll', $tempPath]);
        $process->run();

        $output = json_decode($process->getOutput(), true);

        if (!$process->isSuccessful() || !$output || !isset($output['success']) || !$output['success']) {
            $errorMessage = $output['message'] ?? 'Failed to extract face encoding';
            $rawOutput = $process->getOutput();
            $errorOutput = $process->getErrorOutput();
            
            \Illuminate\Support\Facades\Log::error('Face Recognition Error', [
                'message' => $errorMessage,
                'raw_output' => $rawOutput,
                'error_output' => $errorOutput,
                'command' => $process->getCommandLine()
            ]);

            return [
                'success' => false,
                'message' => $errorMessage . ($errorOutput ? ": " . $errorOutput : ""),
            ];
        }

        $faceEncoding = json_encode($output['encoding']);

        // Store the face image permanently
        $path = $faceImage->store('faces/enrolled', 'public');

        // Update employee record
        $employee->update([
            'face_image_path' => $path,
            'face_encoding' => $faceEncoding,
        ]);

        return [
            'success' => true,
            'message' => 'Face enrolled successfully',
            'path' => $path,
        ];
    }

    /**
     * Verify if uploaded face matches employee's enrolled face.
     * In production, this would use ML library to compare face encodings.
     *
     * @param Employee $employee
     * @param UploadedFile $faceImage
     * @return array
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

        $tempPath = $faceImage->getRealPath();

        // Execute python script to verify face
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
