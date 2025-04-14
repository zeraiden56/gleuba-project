<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;

class CalcularIndice extends Command
{
    protected $signature = 'import:calcular-indice';
    protected $description = 'Calcula o índice geral baseado em critérios predefinidos';

    public function handle()
    {
        $this->info("🧮 Iniciando cálculo de índice e posição...");

        $municipios = Municipio::all();

        foreach ($municipios as $municipio) {
            $nota = 0;

            // UF
            $nota += match($municipio->uf) {
                'MT', 'PA', 'SP', 'SC', 'PR', 'MS', 'GO', 'MG', 'RO' => 10,
                'RS' => 8,
                default => 6,
            };

            // Habitantes
            $pop = $municipio->populacao;
            if ($pop < 20000) $nota += 5;
            elseif ($pop < 80000) $nota += 11;
            elseif ($pop < 200000) $nota += 15;
            else $nota += 13;

            // PIB per capita
            $pib_pc = $municipio->pib_per_capita;
            if ($pib_pc < 10000) $nota += 5;
            elseif ($pib_pc < 25000) $nota += 15;
            else $nota += 20;

            // Crescimento PIB
            $nota += $this->notinha($municipio->cresc_pib_10y, [5, 15, 25]);
            $nota += $this->notinha($municipio->cresc_pib_5y, [5, 15, 25]);

            // Aceleração PIB
            $nota += $this->notinha($municipio->acel_pib, [5, 10, 15]);

            // Curva PIB
            $nota += $this->curva($municipio->curva_pib);

            // Remuneração
            $nota += $this->notinha($municipio->remm_mes, [5, 10, 15]);

            // Crescimento Populacional
            $nota += $this->notinha($municipio->acel_pop, [5, 10, 20]);

            // Curva POP
            $nota += $this->curva($municipio->curva_pop);

            // Violência (inverso)
            $nota += $this->notinhaInvertida($municipio->ind_viol, [9, 5, 0]);

            // Empresas abertas per capita
            $nota += $this->notinha($municipio->abemp_per_capita, [5, 15, 20]);

            // Curva empresas/pop
            $nota += $this->curva($municipio->curva_abemp_pop);

            // Saldo admissões/demissões
            $nota += $this->notinha($municipio->admxdem, [5, 10, 15]);

            // Admissões por população
            $nota += $this->notinha($municipio->admxpop, [5, 10, 15]);

            $municipio->update(['indice' => $nota]);
        }

        // Agora ordena todos e define posição
        $todosOrdenados = Municipio::orderByDesc('indice')->get();
        foreach ($todosOrdenados as $i => $mun) {
            $mun->update(['posicao' => $i + 1]);
        }

        $this->info("✅ Índice e posição atualizados com sucesso!");
    }

    private function notinha($valor, array $pontos)
    {
        if (!is_numeric($valor)) return 0;
        if ($valor < 0) return $pontos[0];
        if ($valor < 20) return $pontos[1];
        return $pontos[2];
    }

    private function notinhaInvertida($valor, array $pontos)
    {
        if (!is_numeric($valor)) return 0;
        if ($valor < 10) return $pontos[0];
        if ($valor < 30) return $pontos[1];
        return $pontos[2];
    }

    private function curva($val)
    {
        return match(strtolower($val)) {
            'baixa' => 5,
            'média', 'media' => 10,
            'alta' => 20,
            default => 0,
        };
    }
}
