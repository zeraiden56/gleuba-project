<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportAdmissoes extends Command
{
    protected $signature = 'import:admissoes';
    protected $description = 'Importa os dados de admissões mais recentes por município';

    public function handle()
    {
        $this->info('📥 Iniciando importação de admissões...');

        $path = storage_path('app/imports/admissoes.xlsx');
        if (!file_exists($path)) {
            $this->error("❌ Arquivo não encontrado em: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Séries');
        if (!$sheet) {
            $this->error("❌ Aba 'Séries' não encontrada no arquivo.");
            return;
        }

        $linhas = $sheet->toArray(null, false, false, false);
        $municipios = Municipio::all()->keyBy('codigo_ibge');
        $atualizados = 0;

        foreach ($linhas as $linha) {
            if (!isset($linha[1])) continue;

            $codigo = str_pad((string)trim($linha[1]), 7, '0', STR_PAD_LEFT);
            $valor = null;

            // Busca retroativa a partir da última coluna (2025.02, índice 65)
            for ($i = 65; $i >= 3; $i--) {
                $raw = $linha[$i] ?? '';
                $raw = str_replace(',', '.', $raw);
                if (is_numeric($raw)) {
                    $valor = (float)$raw;
                    break;
                }
            }

            if ($valor === null || !$municipios->has($codigo)) continue;

            $municipio = $municipios[$codigo];
            $pop = $municipio->populacao;

            $admxpop = ($pop && $pop > 0) ? round(($valor / $pop) * 100, 2) : null;
            $med_admy_vs_pop = ($pop && $pop > 0) ? round($valor / $pop, 6) : null;

            $municipio->update([
                'admy' => $valor,
                'admxpop' => $admxpop,
                'med_admy_vs_pop' => $med_admy_vs_pop,
            ]);

            $atualizados++;
            $this->line("✅ {$municipio->cidade} ({$codigo}) - Admy: {$valor} | admxpop: {$admxpop} | med_admy_vs_pop: {$med_admy_vs_pop}");
        }

        $media = Municipio::whereNotNull('admy')->avg('admy') ?? 0;
        $this->info("📊 Média geral de admissões: " . round($media, 2));
        $this->info("🚀 Importação de admissões finalizada com sucesso. Total atualizados: {$atualizados}");
    }
}
