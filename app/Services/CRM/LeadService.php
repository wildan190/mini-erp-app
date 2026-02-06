<?php

namespace App\Services\CRM;


use App\Models\Lead;


class LeadService
{
    public function index()
    {
        return Lead::paginate(10);
    }

    public function create(array $data): Lead
    {
        return Lead::create($data);
    }
}