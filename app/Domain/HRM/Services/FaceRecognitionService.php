<?php

namespace App\Domain\HRM\Services;

use App\Domain\HRM\Models\Employee;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FaceRecognitionService
{
    private string $faceApiUrl;

    public function __construct()
    {
        $this->faceApiUrl = rtrim(env('FACE_API_URL', 'http://face-api:5000'), '/');
    }

    /**
     * Enroll employee face — stores image and extracts encoding via face-api microservice.
     */
    public function enrollFace(Employee $employee, UploadedFile $faceImage): array
    {
        $imageContent = file_get_contents($faceImage->getRealPath());

        try {
            $response = Http::timeout(30)
                ->attach('image', $imageContent, $faceImage->getClientOriginalName())
                ->post("{$this->faceApiUrl}/encode");
        } catch (\Exception $e) {
            Log::error('Face API unreachable during enroll', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Face recognition service tidak dapat dihubungi. Pastikan face-api container sedang berjalan.',
            ];
        }

        if (!$response->successful()) {
            $body = $response->json();
            $message = $body['message'] ?? 'Gagal mengekstrak encoding wajah.';
            Log::warning('Face API encode failed', ['status' => $response->status(), 'body' => $body]);
            return ['success' => false, 'message' => $message];
        }

        $output = $response->json();

        if (empty($output['encoding'])) {
            return ['success' => false, 'message' => 'Tidak ada wajah yang terdeteksi pada gambar.'];
        }

        $faceEncoding = json_encode($output['encoding']);
        $path = $faceImage->store('faces/enrolled', 'public');

        $employee->update([
            'face_image_path'          => $path,
            'face_encoding'            => $faceEncoding,
            'requires_face_verification' => true,
        ]);

        return [
            'success' => true,
            'message' => 'Wajah berhasil di-enroll.',
            'path'    => $path,
        ];
    }

    /**
     * Verify if uploaded face matches employee's enrolled face via face-api microservice.
     */
    public function verifyFace(Employee $employee, UploadedFile $faceImage): array
    {
        if (empty($employee->face_encoding)) {
            return [
                'verified'   => false,
                'confidence' => 0,
                'message'    => 'Karyawan belum pernah enroll wajah.',
            ];
        }

        $imageContent = file_get_contents($faceImage->getRealPath());

        try {
            $response = Http::timeout(30)
                ->attach('image', $imageContent, $faceImage->getClientOriginalName())
                ->post("{$this->faceApiUrl}/verify", [
                    'target_encoding' => $employee->face_encoding,
                    'tolerance'       => 0.5,
                ]);
        } catch (\Exception $e) {
            Log::error('Face API unreachable during verify', ['error' => $e->getMessage()]);
            return [
                'verified'   => false,
                'confidence' => 0,
                'message'    => 'Face recognition service tidak dapat dihubungi.',
            ];
        }

        if (!$response->successful()) {
            $body = $response->json();
            Log::warning('Face API verify returned error', ['status' => $response->status(), 'body' => $body]);
            return [
                'verified'   => false,
                'confidence' => 0,
                'message'    => $body['message'] ?? 'Verifikasi wajah gagal.',
            ];
        }

        $output = $response->json();

        return [
            'verified'   => $output['verified'] ?? false,
            'confidence' => isset($output['distance']) ? round(1 - $output['distance'], 4) : 0,
            'message'    => $output['message'] ?? 'Verifikasi wajah selesai.',
        ];
    }

    /**
     * Remove employee's face data.
     */
    public function removeFaceData(Employee $employee): bool
    {
        if ($employee->face_image_path) {
            Storage::disk('public')->delete($employee->face_image_path);
        }

        $employee->update([
            'face_image_path'          => null,
            'face_encoding'            => null,
            'requires_face_verification' => false,
        ]);

        return true;
    }

    /**
     * Store attendance face image for audit purposes.
     */
    public function storeAttendanceFaceImage(UploadedFile $faceImage): string
    {
        return $faceImage->store('faces/attendance', 'public');
    }
}
