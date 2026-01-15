<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alocacao extends Model
{
    protected $table = 'alocacoes';
    
    protected $fillable = [
        'escala_id',
        'servidor_id',
        'equipe_id',
        'modulo_id',
        'dia',
        'data',
        'horas',
        'horas_abono',
        'tipo_extra',
        'is_lider',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'date',
        ];
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class);
    }

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }
}
