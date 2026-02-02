<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorasAprovadas extends Model
{
    protected $table = 'horas_aprovadas';
    
    protected $fillable = [
        'servidor_id',
        'escala_id',
        'mes',
        'ano',
        'horas',
    ];

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class);
    }
}
