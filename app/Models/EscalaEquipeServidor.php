<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EscalaEquipeServidor extends Model
{
    protected $table = 'escala_equipe_servidores';
    
    protected $fillable = [
        'escala_id',
        'equipe_id',
        'servidor_id',
        'modulo_id',
        'lider',
    ];

    protected function casts(): array
    {
        return [
            'lider' => 'boolean',
        ];
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class);
    }

    public function equipe(): BelongsTo
    {
        return $this->belongsTo(Equipe::class);
    }

    public function servidor(): BelongsTo
    {
        return $this->belongsTo(Servidor::class);
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }
}
