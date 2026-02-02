<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Unidade extends Model
{
    protected $table = 'unidades';
    
    protected $fillable = [
        'nome',
        'codigo',
        'endereco',
        'telefone',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class);
    }

    public function equipes(): HasMany
    {
        return $this->hasMany(Equipe::class);
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class);
    }

    public function servidores(): HasMany
    {
        return $this->hasMany(Servidor::class);
    }

    public function escalas(): HasMany
    {
        return $this->hasMany(Escala::class);
    }

    public function distribuicoes(): HasMany
    {
        return $this->hasMany(DistribuicaoOrcamento::class);
    }
}
