<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/usuarios",
     *     tags={"Usuarios"},
     *     summary="Obtener lista de usuarios",
     *     description="Retorna todos los usuarios registrados en el sistema",
     *     @OA\Response(
     *         response=200,
     *         description="Lista de usuarios obtenida correctamente"
     *     )
     * )
     */
    public function index()
    {
        return response()->json(Usuario::all());
    }

    /**
     * @OA\Post(
     *     path="/api/usuarios",
     *     tags={"Usuarios"},
     *     summary="Crear un nuevo usuario",
     *     description="Crea un nuevo usuario en el sistema",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nombre","email","password","rol"},
     *             @OA\Property(property="nombre", type="string", example="Juan Pérez"),
     *             @OA\Property(property="email", type="string", format="email", example="juan@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="123456"),
     *             @OA\Property(property="rol", type="string", example="usuario")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario creado correctamente"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        if (!in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".',
                'status' => false
            ], 400);
        }

        $validated['password'] = Hash::make($validated['password']);
        $usuario = Usuario::create($validated);

        if (!$usuario) {
            return response()->json([
                'message' => 'Error al crear el usuario',
                'status' => false
            ], 500);
        }

        return response()->json([
            'message' => 'Usuario creado correctamente',
            'status' => true
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuarios"},
     *     summary="Obtener un usuario por ID",
     *     description="Devuelve los detalles de un usuario específico",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario encontrado"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function show(string $id)
    {
        //
    }

    /**
     * @OA\Put(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuarios"},
     *     summary="Actualizar un usuario",
     *     description="Actualiza los datos de un usuario existente",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="nombre", type="string", example="Pedro García"),
     *             @OA\Property(property="email", type="string", format="email", example="pedro@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="nueva123"),
     *             @OA\Property(property="rol", type="string", example="admin")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario actualizado correctamente"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Error de validación o restricciones de rol"
     *     )
     * )
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->rol !== 'admin') {
            return response()->json([
                'message' => 'Solo los usuarios con rol admin pueden ser actualizados.',
                'status' => false
            ], 422);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'sometimes|required|string',
        ]);

        if (isset($validated['rol']) && !in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".'
            ], 422);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ], 200);
    }

    /**
     * @OA\Delete(
     *     path="/api/usuarios/{id}",
     *     tags={"Usuarios"},
     *     summary="Eliminar un usuario",
     *     description="Elimina un usuario existente del sistema",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID del usuario",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuario eliminado correctamente"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Usuario no encontrado"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        //
    }
}
