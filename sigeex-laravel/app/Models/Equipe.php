<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    protected $table = 'equipes';
    
    protected $fillable = [
        'unidade_id',
        'nome',
        'descricao',
    ];

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function escalaServidores(): HasMany
    {
        return $this->hasMany(EscalaEquipeServidor::class);
    }
}
