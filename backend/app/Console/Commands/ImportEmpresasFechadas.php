<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportEmpresasFechadas extends Command
{
    protected $signature = 'import:empresas-fechadas';
    protected $description = 'Importa os dados de empresas fechadas e calcula indicadores relacionados';

    public function handle()
    {
        $this->info("\n📉 Iniciando importação de empresas fechadas...");

        $path = storage_path('app/imports/empresas-fechadas.xlsx');
        if (!file_exists($path)) {
            $this->error("❌ Arquivo não encontrado: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $municipios = Municipio::all()->keyBy(fn ($m) => strtoupper(trim("{$m->cidade} - {$m->uf}")));
        $dadosPorMunicipio = [];

        foreach ($sheet->getRowIterator(3) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = iterator_to_array($cellIterator, true); // usa letras A, B, C...

            $nomeUf = strtoupper(trim($cells['C']?->getValue() ?? ''));
            $quantidade = str_replace(',', '.', $cells['I']?->getValue() ?? '');

            if (!$nomeUf || !is_numeric($quantidade)) continue;

            if (!isset($dadosPorMunicipio[$nomeUf])) {
                $dadosPorMunicipio[$nomeUf] = 0;
            }

            $dadosPorMunicipio[$nomeUf] += (float)$quantidade;
        }

        foreach ($municipios as $nomeUf => $municipio) {
            $total = $dadosPorMunicipio[$nomeUf] ?? null;
            $pop = $municipio->populacao ?? null;

            $demyVsPop = ($total && $pop && $pop > 0) ? round(($total / $pop) * 100, 5) : null;

            $municipio->update([
                'demy' => $total,
                'demy_vs_pop' => $demyVsPop,
                'demy_vs_med' => null, // preencheremos depois com base em média
            ]);
        }

        // Calcular média geral de demissões
        $mediaDem = Municipio::whereNotNull('demy')->avg('demy');

        // Atualizar demy_vs_med (comparação com a média nacional)
        foreach (Municipio::whereNotNull('demy')->get() as $m) {
            $valor = $m->demy ?? 0;
            $m->update([
                'demy_vs_med' => round(($valor / $mediaDem) * 100, 1),
            ]);
        }

        $this->info("✅ Empresas fechadas importadas com sucesso e indicadores calculados.");
    }
}
