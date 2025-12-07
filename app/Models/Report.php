<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'report_details', 'filters', 'users'];

    protected $casts = [
        'report_details' => 'array',
        'filters' => 'array',
        'users' => 'array',
    ];

    public function getFilterDefinitions(): array
    {
        return $this->filters ?? [];
    }
}

