<?php

namespace App\Services\CRM;

use App\Models\CRM\Prospect;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProspectService
{
    public function index()
    {
        return Prospect::with('customer')->latest()->paginate(10);
    }

    public function show($id): Prospect
    {
        if (is_numeric($id)) {
            return Prospect::with(['customer', 'pipelines'])->findOrFail($id);
        }
        if (Str::isUuid($id)) {
            return Prospect::with(['customer', 'pipelines'])->where('uuid', $id)->firstOrFail();
        }
        abort(404);
    }

    public function create(array $data): Prospect
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['customer_id'])) {
                if (is_numeric($data['customer_id'])) {
                    // already ID, but let's verify or leave as is. Actually better to resolve if it's potentially from a UUID field
                } elseif (Str::isUuid($data['customer_id'])) {
                    $data['customer_id'] = Customer::where('uuid', $data['customer_id'])->value('id');
                }
            }

            $prospect = Prospect::create($data);

            // Log to pipeline
            $prospect->pipelines()->create([
                'stage' => $data['status'] ?? 'new'
            ]);

            return $prospect;
        });
    }

    public function update($id, array $data): Prospect
    {
        return DB::transaction(function () use ($id, $data) {
            if (is_numeric($id)) {
                $prospect = Prospect::findOrFail($id);
            } elseif (Str::isUuid($id)) {
                $prospect = Prospect::where('uuid', $id)->firstOrFail();
            } else {
                abort(404);
            }
            $oldStatus = $prospect->status;

            if (isset($data['customer_id'])) {
                if (is_numeric($data['customer_id'])) {
                    // ID
                } elseif (Str::isUuid($data['customer_id'])) {
                    $data['customer_id'] = Customer::where('uuid', $data['customer_id'])->value('id');
                }
            }

            $prospect->update($data);

            if (isset($data['status']) && $data['status'] !== $oldStatus) {
                $prospect->pipelines()->create([
                    'stage' => $data['status']
                ]);
            }

            return $prospect;
        });
    }

    public function delete($id): bool
    {
        if (is_numeric($id)) {
            $prospect = Prospect::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $prospect = Prospect::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        return $prospect->delete();
    }

    public function updateStatus(Prospect $prospect, string $status): Prospect
    {
        return DB::transaction(function () use ($prospect, $status) {
            $prospect->update(['status' => $status]);

            // Log transition to pipeline
            $prospect->pipelines()->create([
                'stage' => $status
            ]);

            return $prospect;
        });
    }
}