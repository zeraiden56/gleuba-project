<?php

namespace App\Swagger\Schemas;

use OpenApi\Attributes as OA;

/**
 * @OA\Schema(
 *     schema="Municipio",
 *     title="Município",
 *     description="Informações detalhadas sobre o município",
 *     type="object"
 * )
 */
class MunicipioSchema
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: "São Paulo")]
    public string $cidade;

    #[OA\Property(example: "SP")]
    public string $uf;

    #[OA\Property(example: 3550308)]
    public int $codigo_ibge;

    #[OA\Property(example: 1234567)]
    public int $populacao;

    #[OA\Property(example: 55000000000)]
    public float $pib;

    #[OA\Property(example: 45000.55)]
    public float $pib_per_capita;

    #[OA\Property(example: 3750.45)]
    public float $pib_pcpt_mes;

    #[OA\Property(example: 12.5, description: "Crescimento do PIB em 10 anos (%)")]
    public float $cresc_pib_10y;

    #[OA\Property(example: 6.2, description: "Crescimento do PIB em 5 anos (%)")]
    public float $cresc_pib_5y;

    #[OA\Property(example: 3.4, description: "Média do PIB nos últimos 5 anos (R$)")]
    public float $med_pib_5y;

    #[OA\Property(example: 78.5, description: "Índice de violência")]
    public float $ind_viol;

    #[OA\Property(example: 120, description: "Empresas abertas por mil habitantes")]
    public float $abemp_per_capita;
}
