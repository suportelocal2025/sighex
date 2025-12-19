<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LogDistribuicao extends Model
{
    protected $table = 'log_distribuicao';
    
    protected $fillable = [
        'unidade_id',
        'ano',
        'valor_anterior',
        'valor_novo',
        'usuario_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_anterior' => 'decimal:2',
            'valor_novo' => 'decimal:2',
        ];
    }

    public function unidade(): BelongsTo
    {
        return $this->belongsTo(Unidade::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
