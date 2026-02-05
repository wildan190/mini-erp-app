<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Customer extends Model
{
    protected $fillable = ['name', 'email'];


    public function prospects(): HasMany
    {
        return $this->hasMany(Prospect::class);
    }


    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }
}