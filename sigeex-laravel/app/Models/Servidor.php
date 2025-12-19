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
    ];

    protected function casts(): array
    {
        return [
            'apto_escala_extra' => 'boolean',
            'ativo' => 'boolean',
        ];
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
