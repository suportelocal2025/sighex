<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Modulo extends Model
{
    protected $table = 'modulos';
    
    protected $fillable = [
        'unidade_id',
        'nome',
        'descricao',
        'ativo',
    ];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }
}
