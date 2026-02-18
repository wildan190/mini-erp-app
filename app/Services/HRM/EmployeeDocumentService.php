<?php

namespace App\Services\HRM;

use App\Models\HRM\EmployeeDocument;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EmployeeDocumentService
{
    /**
     * Upload and create a new document.
     *
     * @param array $data
     * @param UploadedFile $file
     * @return EmployeeDocument
     */
    public function uploadDocument(array $data, UploadedFile $file): EmployeeDocument
    {
        $path = $file->store('employee_documents', 'public');
        $data['file_path'] = $path;

        return EmployeeDocument::create($data);
    }

    /**
     * Delete a document.
     *
     * @param EmployeeDocument $document
     * @return bool|null
     */
    public function deleteDocument(EmployeeDocument $document): ?bool
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }
    /**
     * Find document by ID or UUID.
     *
     * @param string|int $id
     * @return EmployeeDocument|null
     */
    public function findDocument(string|int $id): ?EmployeeDocument
    {
        if (is_numeric($id)) {
            return EmployeeDocument::find($id);
        }
        if (Str::isUuid($id)) {
            return EmployeeDocument::where('uuid', $id)->first();
        }
        return null;
    }
}
