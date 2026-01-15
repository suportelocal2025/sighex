<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servidor extends Model
{
    protected $table = 'servidores';
    
    protected $fillable = [
        'unidade_id',
        'nome',
        'matricula',
        'cargo',
        'email',
        'telefone',
        'apto_escala_extra',
        'ativo',
        'inativo_inicio',
        'inativo_fim',
        'motivo_inativo',
        'inativo_indefinido',
    ];

    protected function casts(): array
    {
        return [
            'apto_escala_extra' => 'boolean',
            'ativo' => 'boolean',
            'inativo_inicio' => 'date',
            'inativo_fim' => 'date',
            'inativo_indefinido' => 'boolean',
        ];
    }
    
    public function isDisponivelParaEscala(): bool
    {
        if (!$this->ativo || !$this->apto_escala_extra) {
            return false;
        }
        
        if ($this->inativo_indefinido) {
            return false;
        }
        
        if ($this->inativo_inicio && $this->inativo_fim) {
            $hoje = now()->toDateString();
            if ($hoje >= $this->inativo_inicio->toDateString() && $hoje <= $this->inativo_fim->toDateString()) {
                return false;
            }
        }
        
        return true;
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function alocacoes(): HasMany
    {
        return $this->hasMany(Alocacao::class);
    }

    public function escalaEquipes(): HasMany
    {
        return $this->hasMany(EscalaEquipeServidor::class);
    }
}
