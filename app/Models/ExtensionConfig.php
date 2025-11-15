<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExtensionConfig extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension_id',
        'key',
        'value',
        'is_encrypted',
    ];

    protected $casts = [
        'is_encrypted' => 'boolean',
    ];

    // Relasi
    public function extension(): BelongsTo
    {
        return $this->belongsTo(Extension::class);
    }

    // Accessor untuk decrypt value otomatis jika encrypted
    public function getDecryptedValueAttribute()
    {
        return $this->is_encrypted ? decrypt($this->value) : $this->value;
    }
}
