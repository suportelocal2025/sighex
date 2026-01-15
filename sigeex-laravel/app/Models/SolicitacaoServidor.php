<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolicitacaoServidor extends Model
{
    protected $table = 'solicitacao_servidores';
    
    protected $fillable = [
        'matricula',
        'nome',
        'unidade_id',
        'cargo',
        'solicitante_id',
        'status',
        'aprovador_id',
        'motivo_rejeicao',
        'data_aprovacao',
    ];

    protected function casts(): array
    {
        return [
            'data_aprovacao' => 'datetime',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function solicitante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'solicitante_id');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'aprovador_id');
    }
}
