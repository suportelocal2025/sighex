<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Escala extends Model
{
    protected $table = 'escalas';
    
    protected $fillable = [
        'unidade_id',
        'mes',
        'ano',
        'status',
        'motivo_rejeicao',
        'data_rejeicao',
        'valor_executado',
        'criado_por',
        'aprovado_por',
        'data_envio',
        'data_aprovacao',
        'usa_margem',
        'excede_margem',
        'requer_aprovacao_super',
        'valor_previsto',
        'orcamento_mes',
        'limite_margem',
    ];

    protected function casts(): array
    {
        return [
            'valor_executado' => 'decimal:2',
            'valor_previsto' => 'decimal:2',
            'orcamento_mes' => 'decimal:2',
            'limite_margem' => 'decimal:2',
            'data_envio' => 'datetime',
            'data_aprovacao' => 'datetime',
            'data_rejeicao' => 'datetime',
            'usa_margem' => 'boolean',
            'excede_margem' => 'boolean',
            'requer_aprovacao_super' => 'boolean',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function criador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'criado_por');
    }

    public function aprovador(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'aprovado_por');
    }

    public function alocacoes(): HasMany
    {
        return $this->hasMany(Alocacao::class);
    }

    public function equipeServidores(): HasMany
    {
        return $this->hasMany(EscalaEquipeServidor::class);
    }

    public function isRascunho(): bool
    {
        return $this->status === 'rascunho';
    }

    public function isPendente(): bool
    {
        return $this->status === 'pendente';
    }

    public function isAprovada(): bool
    {
        return $this->status === 'aprovada';
    }

    public function isRejeitada(): bool
    {
        return $this->status === 'rejeitada';
    }

    public function isExecutada(): bool
    {
        return $this->status === 'executada';
    }

    public function getTotalHorasAttribute(): int
    {
        return $this->alocacoes()->sum('horas');
    }
}
