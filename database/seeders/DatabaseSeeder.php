<?php

namespace Database\Seeders;

use App\Models\Usuario;
use App\Models\Unidade;
use App\Models\Equipe;
use App\Models\Modulo;
use App\Models\OrcamentoGlobal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $unidade = Unidade::create([
            'nome' => 'Penitenciária Central',
            'codigo' => 'PEN-001',
            'endereco' => 'Rua Principal, 100',
            'telefone' => '(11) 1234-5678',
            'ativo' => true,
        ]);

        foreach (['A', 'B', 'C', 'D'] as $letra) {
            Equipe::create([
                'unidade_id' => $unidade->id,
                'nome' => "Equipe $letra",
                'descricao' => "Equipe de plantão $letra",
            ]);
        }

        foreach (['Raio 1', 'Raio 2', 'Raio 3', 'Portaria'] as $modulo) {
            Modulo::create([
                'unidade_id' => $unidade->id,
                'nome' => $modulo,
                'ativo' => true,
            ]);
        }

        Usuario::create([
            'nome' => 'Superintendente',
            'email' => 'super@sistema.gov.br',
            'senha' => Hash::make('admin123'),
            'papel' => 'superintendente',
            'ativo' => true,
        ]);

        Usuario::create([
            'nome' => 'Diretor Unidade',
            'email' => 'diretor@sistema.gov.br',
            'senha' => Hash::make('admin123'),
            'papel' => 'diretor',
            'unidade_id' => $unidade->id,
            'ativo' => true,
        ]);

        Usuario::create([
            'nome' => 'Recursos Humanos',
            'email' => 'rh@sistema.gov.br',
            'senha' => Hash::make('admin123'),
            'papel' => 'rh',
            'ativo' => true,
        ]);

        Usuario::create([
            'nome' => 'Administrativo',
            'email' => 'admin@sistema.gov.br',
            'senha' => Hash::make('admin123'),
            'papel' => 'administrativo',
            'ativo' => true,
        ]);

        OrcamentoGlobal::create([
            'ano' => date('Y'),
            'valor_total' => 1000000.00,
            'reserva_tecnica_percentual' => 10.00,
        ]);
    }
}
