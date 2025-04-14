<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Municipio;
use GuzzleHttp\Client;

class ImportMunicipios extends Command
{
    protected $signature = 'import:municipios';
    protected $description = 'Importa os dados dos municípios da API do IBGE e insere no banco de dados';

    public function handle()
    {
        $client = new Client();
        $this->info("Buscando lista de municípios da API do IBGE...");

        $response = $client->get('https://servicodados.ibge.gov.br/api/v1/localidades/municipios');
        $municipios = json_decode($response->getBody()->getContents(), true);

        $this->info("Iniciando importação...");

        foreach ($municipios as $m) {
            $codigoIbge = $m['id'];
            $cidade = $m['nome'];
            $uf = $m['microrregiao']['mesorregiao']['UF']['sigla'];

            $municipio = Municipio::where('codigo_ibge', $codigoIbge)->first();

            $popAtual = $municipio?->populacao ?? null;
            $pibAtual = $municipio?->pib ?? null;

            // --- População atual (2022 - Tabela 4714)
            if (!$popAtual) {
                try {
                    $res = $client->get("https://apisidra.ibge.gov.br/values/t/4714/n6/{$codigoIbge}");
                    $dados = json_decode($res->getBody()->getContents(), true);
                    $popAtual = (int) str_replace(['.', ','], ['', '.'], $dados[1]['V']);
                } catch (\Exception $e) {
                    $this->error("Erro POP atual {$cidade}: {$e->getMessage()}");
                }
            }

            // --- PIB atual (2021 - Tabela 5938 / var 37)
            if (!$pibAtual) {
                try {
                    $res = $client->get("https://apisidra.ibge.gov.br/values/t/5938/n6/{$codigoIbge}/v/37/p/2021");
                    $dados = json_decode($res->getBody()->getContents(), true);
                    $pibAtual = (float) str_replace(['.', ','], ['', '.'], $dados[1]['V']) * 1000;
                } catch (\Exception $e) {
                    $this->error("Erro PIB atual {$cidade}: {$e->getMessage()}");
                }
            }

            // --- PIB 2011
            $pib2011 = null;
            try {
                $res = $client->get("https://apisidra.ibge.gov.br/values/t/5938/n6/{$codigoIbge}/v/37/p/2011");
                $dados = json_decode($res->getBody()->getContents(), true);
                $pib2011 = (float) str_replace(['.', ','], ['', '.'], $dados[1]['V']) * 1000;
            } catch (\Exception $e) {}

            // --- PIB 2016
            $pib2016 = null;
            try {
                $res = $client->get("https://apisidra.ibge.gov.br/values/t/5938/n6/{$codigoIbge}/v/37/p/2016");
                $dados = json_decode($res->getBody()->getContents(), true);
                $pib2016 = (float) str_replace(['.', ','], ['', '.'], $dados[1]['V']) * 1000;
            } catch (\Exception $e) {}

            // --- População 2010 (Tabela 200)
            $pop2010 = null;
            try {
                $res = $client->get("https://apisidra.ibge.gov.br/values/t/200/n6/{$codigoIbge}/p/2010");
                $dados = json_decode($res->getBody()->getContents(), true);
                $pop2010 = (int) str_replace(['.', ','], ['', '.'], $dados[1]['V']);
            } catch (\Exception $e) {}

            // --- População 2000 (Tabela 200)
            $pop2000 = null;
            try {
                $res = $client->get("https://apisidra.ibge.gov.br/values/t/200/n6/{$codigoIbge}/p/2000");
                $dados = json_decode($res->getBody()->getContents(), true);
                $pop2000 = (int) str_replace(['.', ','], ['', '.'], $dados[1]['V']);
            } catch (\Exception $e) {}

            // --- Cálculos
            $pib_per_capita = ($pibAtual && $popAtual && $popAtual > 0) ? round($pibAtual / $popAtual, 2) : null;
            $pib_pcpt_mes = $pib_per_capita ? round($pib_per_capita / 12, 2) : null;

            $cresc_pib_10y = ($pibAtual && $pib2011) ? round((($pibAtual - $pib2011) / $pib2011) * 100, 2) : null;
            $cresc_pib_5y = ($pibAtual && $pib2016) ? round((($pibAtual - $pib2016) / $pib2016) * 100, 2) : null;

            $med_pib_10y = ($pibAtual && $pib2011) ? round(($pibAtual + $pib2011) / 2, 2) : null;
            $med_pib_5y = ($pibAtual && $pib2016) ? round(($pibAtual + $pib2016) / 2, 2) : null;

            $acel_pib = ($pibAtual && $pib2016) ? round(($pibAtual - $pib2016) / 5, 2) : null;
            $curva_pib = $acel_pib !== null ? match (true) {
                $acel_pib < 0 => 'Recessiva',
                $acel_pib > 500000000 => 'Explosiva',
                $acel_pib > 100000000 => 'Crescente',
                default => 'Estável'
            } : null;

            $med_pop30 = ($pop2000 && $pop2010) ? round(($pop2000 + $pop2010) / 2, 2) : null;
            $med_pop5 = ($pop2010 && $popAtual) ? round(($pop2010 + $popAtual) / 2, 2) : null;
            $acel_pop = ($popAtual && $pop2010) ? round(($popAtual - $pop2010) / 5, 2) : null;
            $curva_pop = $acel_pop !== null ? match (true) {
                $acel_pop < 0 => 'Decrescente',
                $acel_pop > 50000 => 'Alta',
                $acel_pop > 10000 => 'Média',
                default => 'Baixa'
            } : null;

            Municipio::updateOrCreate(
                ['codigo_ibge' => $codigoIbge],
                [
                    'cidade' => $cidade,
                    'uf' => $uf,
                    'populacao' => $popAtual,
                    'pib' => $pibAtual,
                    'pib_per_capita' => $pib_per_capita,
                    'pib_pcpt_mes' => $pib_pcpt_mes,
                    'cresc_pib_10y' => $cresc_pib_10y,
                    'cresc_pib_5y' => $cresc_pib_5y,
                    'med_pib_10y' => $med_pib_10y,
                    'med_pib_5y' => $med_pib_5y,
                    'acel_pib' => $acel_pib,
                    'curva_pib' => $curva_pib,
                    'pop5y' => $pop2010,
                    'pop30y' => $pop2000,
                    'med_pop30' => $med_pop30,
                    'med_pop5' => $med_pop5,
                    'acel_pop' => $acel_pop,
                    'curva_pop' => $curva_pop
                ]
            );

            $this->info("✅ {$cidade} atualizado.");
        }

        $this->info("\n🚀 Importação finalizada com sucesso.");
        return 0;
    }
}