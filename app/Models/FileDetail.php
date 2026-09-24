<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'file_id',
        'dd_reference',
        'sort_code',
        'account_number',
        'account_name',
        'amount',
        'due_date',
        'bacs_code',
        'status',
        'mandate_status',
        'error_message',
        'forename',
        'surname',
        'initial',
    ];

    protected $casts = [
        'amount'    => 'decimal:2',
        'due_date'  => 'date',
    ];

    // Relationship: Belongs to File
    public function file()
    {
        return $this->belongsTo(File::class);
    }
}