<?php

namespace App\Services\CRM;


use App\Models\Lead;


class LeadService
{
    public function create(array $data): Lead
    {
        return Lead::create($data);
    }
}