<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportDemissoes extends Command
{
    protected $signature = 'import:demissoes';
    protected $description = 'Importa os dados de demissões mais recentes por município e calcula indicadores relacionados';

    public function handle()
    {
        $this->info('📥 Iniciando importação de demissões...');

        $path = storage_path('app/imports/demissoes.xlsx');
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

        // Para cálculo de demy_vs_med
        $valoresDemy = [];

        foreach ($linhas as $linha) {
            if (!isset($linha[1])) continue;

            $codigo = str_pad((string)trim($linha[1]), 7, '0', STR_PAD_LEFT);
            $valor = null;

            // Busca retroativa da coluna mais recente (2025.02 = índice 65)
            for ($i = 65; $i >= 3; $i--) {
                $raw = $linha[$i] ?? '';
                $raw = str_replace(',', '.', $raw);
                if (is_numeric($raw)) {
                    $valor = (float)$raw;
                    break;
                }
            }

            if ($valor === null || !$municipios->has($codigo)) continue;

            $municipios[$codigo]->demy = $valor;
            $valoresDemy[] = $valor;
        }

        $mediaGeral = count($valoresDemy) ? array_sum($valoresDemy) / count($valoresDemy) : 0;

        // Segunda rodada: atualizar DB com indicadores derivados
        foreach ($municipios as $municipio) {
            if (!isset($municipio->demy)) continue;

            $demy = $municipio->demy;
            $pop = $municipio->populacao;
            $admy = $municipio->admy;

            $demy_vs_pop = ($pop && $pop > 0) ? round(($demy / $pop) * 100, 2) : null;
            $demy_vs_med = ($mediaGeral > 0) ? round($demy / $mediaGeral, 4) : null;
            $admxdem = ($admy !== null && $demy !== null) ? $admy - $demy : null;

            $municipio->update([
                'demy' => $demy,
                'demy_vs_pop' => $demy_vs_pop,
                'demy_vs_med' => $demy_vs_med,
                'admxdem' => $admxdem,
            ]);

            $atualizados++;
            $this->line("✅ {$municipio->cidade} ({$municipio->codigo_ibge}) - Demy: {$demy} | demy_vs_pop: {$demy_vs_pop} | demy_vs_med: {$demy_vs_med}");
        }

        $this->info("📊 Média geral de demissões: " . round($mediaGeral, 2));
        $this->info("🚀 Importação de demissões finalizada com sucesso. Total atualizados: {$atualizados}");
    }
}
