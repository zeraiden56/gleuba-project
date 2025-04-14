<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportEmpresasAbertas extends Command
{
    protected $signature = 'import:empresas-abertas';
    protected $description = 'Importa os dados de empresas abertas e calcula indicadores relacionados';

    public function handle()
    {
        $this->info("\n📅 Iniciando importação de empresas abertas...");

        $path = storage_path('app/imports/empresas-abertas.xlsx');
        if (!file_exists($path)) {
            $this->error("Arquivo não encontrado: $path");
            return;
        }

        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheetByName('MyWorkSheet-1');
        if (!$sheet) {
            $this->error("❌ Aba 'Séries' não encontrada.");
            return;
        }

        $municipios = Municipio::all()->keyBy(fn ($m) => strtoupper(trim("{$m->cidade} - {$m->uf}")));
        $dadosPorMunicipio = [];

        foreach ($sheet->getRowIterator(3) as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(false);
            $cells = iterator_to_array($cellIterator, true); // indexed by column letters

            $nomeUf = strtoupper(trim($cells['C']?->getValue() ?? '')); // Coluna C = nome município - UF
            $quantidade = str_replace(',', '.', $cells['J']?->getValue() ?? ''); // Coluna J = quantidade


            if (!$nomeUf || !is_numeric($quantidade)) continue;

            if (!isset($dadosPorMunicipio[$nomeUf])) {
                $dadosPorMunicipio[$nomeUf] = 0;
            }

            $dadosPorMunicipio[$nomeUf] += (float)$quantidade;
        }

        $populacoes = [];
        foreach ($municipios as $nomeUf => $municipio) {
            $total = $dadosPorMunicipio[$nomeUf] ?? null;
            $pop = $municipio->populacao ?? null;
            $perCapita = ($total && $pop && $pop > 0) ? round($total / $pop, 5) : null;

            $municipio->update([
                'abert_emp' => $total,
                'abemp_per_capita' => $perCapita,
            ]);

            if ($perCapita !== null) {
                $populacoes[$municipio->uf][] = [
                    'id' => $municipio->id,
                    'valor' => $perCapita,
                ];
            }
        }

        // Ranking por estado baseado em per capita
        foreach ($populacoes as $uf => $dados) {
            usort($dados, fn($a, $b) => $b['valor'] <=> $a['valor']);

            foreach ($dados as $rank => $info) {
                Municipio::find($info['id'])->update([
                    'rankemp_uf_pcap' => $rank + 1,
                ]);
            }

            // Curva
            $valores = array_column($dados, 'valor');
            sort($valores);
            $total = count($valores);
            $p33 = $valores[(int)($total * 0.33)] ?? null;
            $p66 = $valores[(int)($total * 0.66)] ?? null;

            foreach ($dados as $info) {
                $municipio = Municipio::find($info['id']);
                $v = $info['valor'];

                $curva = match (true) {
                    $v <= $p33 => 'Baixa',
                    $v <= $p66 => 'Média',
                    default => 'Alta',
                };

                $municipio->update(['curva_abemp_pop' => $curva]);
            }
        }

        $this->info("✅ Empresas abertas importadas com sucesso e indicadores calculados.");
    }
}
