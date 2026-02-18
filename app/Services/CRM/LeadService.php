<?php

namespace App\Services\CRM;

use App\Models\CRM\Lead;
use App\Models\CRM\Prospect;
use App\Models\CRM\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LeadService
{
    public function index()
    {
        return Lead::latest()->paginate(10);
    }

    public function show($id): Lead
    {
        if (is_numeric($id)) {
            return Lead::findOrFail($id);
        }
        if (Str::isUuid($id)) {
            return Lead::where('uuid', $id)->firstOrFail();
        }
        abort(404);
    }

    public function create(array $data): Lead
    {
        return Lead::create($data);
    }

    public function update($id, array $data): Lead
    {
        if (is_numeric($id)) {
            $lead = Lead::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $lead = Lead::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        $lead->update($data);
        return $lead;
    }

    public function delete($id): bool
    {
        if (is_numeric($id)) {
            $lead = Lead::findOrFail($id);
        } elseif (Str::isUuid($id)) {
            $lead = Lead::where('uuid', $id)->firstOrFail();
        } else {
            abort(404);
        }
        return $lead->delete();
    }

    public function convertToProspect(Lead $lead): Prospect
    {
        return DB::transaction(function () use ($lead) {
            // Find existing customer by email if email exists
            $customer = null;
            if ($lead->email) {
                $customer = Customer::where('email', $lead->email)->first();
            }

            if (!$customer) {
                $customer = Customer::create([
                    'name' => $lead->lead_name,
                    'email' => $lead->email,
                    'phone' => $lead->phone,
                    'company_name' => $lead->company,
                    'customer_type' => 'corporate',
                    'status' => 'active',
                    'currency' => 'IDR'
                ]);
            }

            $prospect = Prospect::create([
                'customer_id' => $customer->id,
                'title' => 'Opportunity from ' . $lead->lead_name,
                'status' => 'new',
                'notes' => $lead->notes
            ]);

            $lead->update(['status' => 'converted']);

            return $prospect;
        });
    }
}