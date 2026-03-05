<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;


class ServiceController extends Controller
{
    /**
     * GET /services
     * Lista solo los servicios activos (excluye soft-deleted)
     */
    public function index(Request $request): JsonResponse
    {
        $services = Service::where('user_id', $request->user()->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'total'   => $services->count(),
            'data'    => $services,
        ]);
    }

    /**
     * POST /services
     * Crea un nuevo servicio con foto en Base64
     */
    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = Service::create([
            'user_id'      => $request->user()->id,
            'name'         => $request->name,
            'description'  => $request->description,
            'foto_persona' => $request->foto_persona,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Servicio creado exitosamente.',
            'data'    => $service,
        ], 201);
    }

    /**
     * GET /services/{id}
     * Muestra un servicio específico
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $service,
        ]);
    }

    /**
     * PUT/PATCH /services/{id}
     * Actualiza un servicio existente
     */
    public function update(UpdateServiceRequest $request, int $id): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado.',
            ], 404);
        }

        $service->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Servicio actualizado exitosamente.',
            'data'    => $service,
        ]);
    }

    /**
     * DELETE /services/{id}
     * Soft Delete: llena deleted_at, NO borra de la BD
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $service = Service::where('user_id', $request->user()->id)
            ->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Servicio no encontrado.',
            ], 404);
        }

        $service->delete(); // Solo marca deleted_at

        return response()->json([
            'success' => true,
            'message' => 'Servicio eliminado correctamente.',
        ]);
    }
}