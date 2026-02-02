<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Usuario extends Authenticatable
{
    protected $table = 'usuarios';
    
    protected $fillable = [
        'nome',
        'email',
        'telefone',
        'matricula',
        'senha',
        'papel',
        'unidade_id',
        'ativo',
    ];

    protected $hidden = [
        'senha',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function isSuperintendente(): bool
    {
        return $this->papel === 'superintendente';
    }

    public function isDiretor(): bool
    {
        return $this->papel === 'diretor';
    }

    public function isRh(): bool
    {
        return $this->papel === 'rh';
    }

    public function isAdministrativo(): bool
    {
        return $this->papel === 'administrativo';
    }
}
