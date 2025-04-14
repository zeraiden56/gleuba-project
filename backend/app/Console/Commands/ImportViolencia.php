<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportViolencia extends Command
{
    protected $signature = 'import:violencia';
    protected $description = 'Importa a taxa de homicídios mais recente por município';

    public function handle()
    {
        $this->info('📊 Iniciando importação da taxa de violência...');

        $path = storage_path('app/imports/taxa-homicidios.xlsx');
        if (!file_exists($path)) {
            $this->error("❌ Arquivo não encontrado em: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);

        // Seleciona a aba correta (verifique o nome se der erro!)
        $sheet = $spreadsheet->getSheetByName('Séries');
        if (!$sheet) {
            $this->error("❌ Aba 'Séries' não encontrada.");
            return;
        }

        $linhas = $sheet->toArray(null, true, true, true);
        $municipios = Municipio::all()->keyBy('codigo_ibge');
        $atualizados = 0;

        foreach ($linhas as $linha) {
            $codigo = (int)($linha['B'] ?? 0);
            if (!$codigo || !isset($municipios[$codigo])) continue;

            // Pega todos os valores a partir da coluna D em diante (ignora sigla, nome, código)
            $valores = array_slice($linha, 4);
            $valoresNumericos = array_filter($valores, function ($v) {
                $v = str_replace(',', '.', $v);
                return is_numeric($v) && (float)$v > 0;
            });

            if (empty($valoresNumericos)) continue;

            $valorMaisRecente = (float)str_replace(',', '.', end($valoresNumericos));

            $municipio = $municipios[$codigo];
            $municipio->ind_viol = $valorMaisRecente;
            $municipio->save();

            $atualizados++;
            $this->line("✅ {$municipio->cidade} ({$codigo}) - Taxa: {$valorMaisRecente}");
        }

        $this->info("🚀 Importação concluída com sucesso. Total de municípios atualizados: {$atualizados}");
    }
}
