<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaDiretor extends Model
{
    protected $table = 'alertas_diretor';
    
    protected $fillable = [
        'unidade_id',
        'escala_id',
        'tipo',
        'titulo',
        'mensagem',
        'mes',
        'ano',
        'prazo_limite',
        'lido',
        'email_enviado',
        'email_enviado_em',
    ];

    protected function casts(): array
    {
        return [
            'prazo_limite' => 'datetime',
            'lido' => 'boolean',
            'email_enviado' => 'boolean',
            'email_enviado_em' => 'datetime',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function escala(): BelongsTo
    {
        return $this->belongsTo(Escala::class);
    }
}
