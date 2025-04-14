<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class TestaIndicadorCacoal extends Command
{
    protected $signature = 'debug:cacoal';
    protected $description = 'Testa leitura da planilha para o município de Cacoal';

    public function handle()
    {
        $this->info("🔍 Lendo planilha de admissões para Cacoal...");

        $codigo = '1100049'; // Cacoal
        $arquivo = storage_path('app/imports/admissoes.xlsx');

        $spreadsheet = IOFactory::load($arquivo);
        $sheet = $spreadsheet->getActiveSheet();
        $linhas = $sheet->toArray();

        foreach ($linhas as $linha) {
            if ((string)($linha[1] ?? '') === $codigo) {
                $valores = array_slice($linha, 3);

                $valoresNumericos = array_map(function ($valor) {
                    $valor = trim((string)$valor);
                    $valor = str_replace(['.', ','], ['', '.'], $valor);
                    return is_numeric($valor) ? (float)$valor : 0;
                }, $valores);

                $total = array_sum($valoresNumericos);
                $this->info("✅ Total de admissões em Cacoal: {$total}");
                return Command::SUCCESS;
            }
        }

        $this->warn("⚠️ Cacoal ({$codigo}) não encontrado na planilha.");
        return Command::FAILURE;
    }
}
