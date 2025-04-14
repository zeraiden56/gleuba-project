<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportRemuneracao extends Command
{
    protected $signature = 'import:remuneracao';
    protected $description = 'Importa a remuneração média no setor formal por município (último ano disponível)';

    public function handle()
    {
        $this->info('💼 Iniciando importação da remuneração...');

        $path = storage_path('app/imports/remuneracao.xlsx');
        if (!file_exists($path)) {
            $this->error("❌ Arquivo não encontrado: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('Séries') ?? $spreadsheet->getActiveSheet();
        $linhas = $sheet->toArray();

        $municipios = Municipio::all()->keyBy(fn($m) => strtoupper($m->cidade));
        $atualizados = 0;

        foreach ($linhas as $linha) {
            $territorio = strtoupper(trim(preg_replace('/\s*\(.*?\)/', '', $linha[0] ?? '')));
            $valor = $linha[12] ?? null; // Coluna M (2022 ou 2017, dependendo da planilha)

            if (!$territorio || !$valor || !isset($municipios[$territorio])) continue;

            $municipio = $municipios[$territorio];
            $municipio->remm_mes = is_numeric($valor) ? (float)str_replace(',', '.', $valor) : null;
            $municipio->save();

            $atualizados++;
            $this->line("✅ {$municipio->cidade} atualizado: R$ {$municipio->remm_mes}");
        }

        $this->info("🚀 Importação concluída. Municípios atualizados: {$atualizados}");
    }
}
