<?php

namespace App\Http\Controllers;

use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="Ranking de Municípios | IBGE API",
 *     description="API para análise e ranking de municípios brasileiros com base em dados socioeconômicos e de desenvolvimento.",
 *     @OA\Contact(
 *         email="arthurcostadias1@gmail.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url="http://127.0.0.1:8000",
 *     description="Servidor local"
 * )
 */
class SwaggerController
{
    // Esse controller não precisa de métodos, só da anotação acima.
}
