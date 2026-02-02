<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Escala;
use App\Models\AlertaDiretor;
use App\Models\User;
use App\Mail\AlertaDiretorMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class VerificarPrazoCorrecaoEscala extends Command
{
    protected $signature = 'escalas:verificar-prazo-correcao';
    protected $description = 'Verifica prazo de correção de escalas rejeitadas e gera alertas';

    public function handle()
    {
        $agora = Carbon::now();
        
        $escalasRejeitadas = Escala::where('status', 'rejeitada')
            ->whereNotNull('data_rejeicao')
            ->get();
        
        $alertasCriados = 0;
        
        foreach ($escalasRejeitadas as $escala) {
            $dataRejeicao = Carbon::parse($escala->data_rejeicao);
            $prazoLimite = $dataRejeicao->copy()->addHours(24);
            $horasRestantes = $agora->diffInHours($prazoLimite, false);
            
            if ($horasRestantes < 0) {
                continue;
            }
            
            $alertaImediato = AlertaDiretor::where('escala_id', $escala->id)
                ->where('tipo', 'correcao_imediata')
                ->exists();
            
            if (!$alertaImediato) {
                AlertaDiretor::create([
                    'unidade_id' => $escala->unidade_id,
                    'escala_id' => $escala->id,
                    'tipo' => 'correcao_imediata',
                    'titulo' => 'Escala Rejeitada - Correção Necessária',
                    'mensagem' => "A escala de " . $this->getNomeMes($escala->mes) . "/{$escala->ano} foi rejeitada pelo RH. Motivo: {$escala->motivo_rejeicao}. Você tem 24 horas para fazer as correções.",
                    'mes' => $escala->mes,
                    'ano' => $escala->ano,
                    'prazo_limite' => $prazoLimite,
                ]);
                $alertasCriados++;
                $this->enviarEmailDiretores($escala, 'correcao_imediata', 24);
            }
            
            if ($horasRestantes <= 6 && $horasRestantes > 0) {
                $alerta6h = AlertaDiretor::where('escala_id', $escala->id)
                    ->where('tipo', 'correcao_6horas')
                    ->exists();
                
                if (!$alerta6h) {
                    AlertaDiretor::create([
                        'unidade_id' => $escala->unidade_id,
                        'escala_id' => $escala->id,
                        'tipo' => 'correcao_6horas',
                        'titulo' => 'URGENTE: Prazo de Correção - 6 horas',
                        'mensagem' => "URGENTE: Restam apenas {$horasRestantes} horas para corrigir a escala de " . $this->getNomeMes($escala->mes) . "/{$escala->ano}. Corrija imediatamente!",
                        'mes' => $escala->mes,
                        'ano' => $escala->ano,
                        'prazo_limite' => $prazoLimite,
                    ]);
                    $alertasCriados++;
                    $this->enviarEmailDiretores($escala, 'correcao_6horas', $horasRestantes);
                }
            }
        }
        
        $this->info("Alertas de prazo de correção criados: {$alertasCriados}");
        return Command::SUCCESS;
    }
    
    private function getNomeMes($mes): string
    {
        $meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 
                  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        return $meses[$mes] ?? '';
    }
    
    private function enviarEmailDiretores(Escala $escala, string $tipo, int $horasRestantes): void
    {
        $alerta = AlertaDiretor::where('escala_id', $escala->id)
            ->where('tipo', $tipo)
            ->first();
        
        if (!$alerta) {
            return;
        }
        
        $diretores = User::where('unidade_id', $escala->unidade_id)
            ->where('perfil', 'diretor')
            ->where('ativo', true)
            ->whereNotNull('email')
            ->get();
        
        foreach ($diretores as $diretor) {
            try {
                Mail::to($diretor->email)->send(new AlertaDiretorMail($alerta));
                $this->info("Email enviado para {$diretor->email} sobre {$tipo}");
                
                if (!$alerta->email_enviado) {
                    $alerta->update([
                        'email_enviado' => true,
                        'email_enviado_em' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error("Erro ao enviar email para {$diretor->email}: " . $e->getMessage());
                $this->error("Erro ao enviar email para {$diretor->email}: " . $e->getMessage());
            }
        }
    }
}
