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
        
        return true;
    }
    
    public function getStatusInatividade(): ?string
    {
        if ($this->inativo_indefinido) {
            return 'Inativo por tempo indeterminado' . ($this->motivo_inativo ? ': ' . $this->motivo_inativo : '');
        }
        
        if ($this->inativo_inicio && $this->inativo_fim) {
            $hoje = now();
            if ($hoje >= $this->inativo_inicio && $hoje <= $this->inativo_fim) {
                return 'Inativo até ' . $this->inativo_fim->format('d/m/Y') . ($this->motivo_inativo ? ': ' . $this->motivo_inativo : '');
            }
        }
        
        return null;
    }
    
    public function isInativoNaData($data): bool
    {
        if (!$this->ativo) {
            return true;
        }
        
        if ($this->inativo_indefinido) {
            return true;
        }
        
        if ($this->inativo_inicio && $this->inativo_fim) {
            $dataVerificar = $data instanceof \Carbon\Carbon ? $data : \Carbon\Carbon::parse($data);
            if ($dataVerificar >= $this->inativo_inicio && $dataVerificar <= $this->inativo_fim) {
                return true;
            }
        }
        
        return false;
    }
    
    public function getMotivoInativoNaData($data): ?string
    {
        if (!$this->ativo && !$this->inativo_indefinido && !$this->inativo_inicio) {
            return 'Servidor inativo';
        }
        
        if ($this->inativo_indefinido) {
            return $this->motivo_inativo ?? 'Inativo por tempo indeterminado';
        }
        
        if ($this->inativo_inicio && $this->inativo_fim) {
            $dataVerificar = $data instanceof \Carbon\Carbon ? $data : \Carbon\Carbon::parse($data);
            if ($dataVerificar >= $this->inativo_inicio && $dataVerificar <= $this->inativo_fim) {
                $motivo = $this->motivo_inativo ?? 'Inativo';
                return $motivo . ' (de ' . $this->inativo_inicio->format('d/m/Y') . ' a ' . $this->inativo_fim->format('d/m/Y') . ')';
            }
        }
        
        return null;
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
