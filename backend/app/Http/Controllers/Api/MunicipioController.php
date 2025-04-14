<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Municipio;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class MunicipioController extends Controller
{
    #[OA\Get(
        path: "/api/municipios",
        summary: "Lista todos os municípios",
        tags: ["Municípios"],
        responses: [
            new OA\Response(
                response: 200,
                description: "Lista de municípios retornada com sucesso",
                content: new OA\JsonContent(
                    type: "array",
                    items: new OA\Items(ref: "#/components/schemas/Municipio")
                )
            )
        ]
    )]
    public function index(Request $request)
    {
        $query = Municipio::query();

        // Filtro por cidade ou UF
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('cidade', 'ILIKE', "%$search%")
                ->orWhere('uf', 'ILIKE', "%$search%");
            });
        }

        // Ordenação por índice decrescente
        $query->orderByDesc('indice');

        // Paginação
        return response()->json(
            $query->paginate(50)
        );
    }


    #[OA\Get(
        path: "/api/municipios/{id}",
        summary: "Exibe dados de um município específico",
        tags: ["Municípios"],
        parameters: [
            new OA\Parameter(
                name: "id",
                in: "path",
                required: true,
                schema: new OA\Schema(type: "integer")
            )
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: "Município encontrado",
                content: new OA\JsonContent(ref: "#/components/schemas/Municipio")
            ),
            new OA\Response(response: 404, description: "Município não encontrado")
        ]
    )]
    public function show($id)
    {
        return response()->json(Municipio::findOrFail($id));
    }
}
