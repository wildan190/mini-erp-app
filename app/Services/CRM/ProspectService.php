<?php

namespace App\Services\CRM;


use App\Models\Prospect;


class ProspectService
{

    public function index()
    {
        return Prospect::paginate(10);
    }

    public function create(array $data): Prospect
    {
        return Prospect::create($data);
    }


    public function updateStatus(Prospect $prospect, string $status): Prospect
    {
        $prospect->update(['status' => $status]);
        return $prospect;
    }
}