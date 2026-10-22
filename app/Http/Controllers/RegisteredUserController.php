<?php

namespace App\Http\Controllers;

use App\RegisteredUser;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    /**
     * Listar usuarios de registro
     */
    public function index()
    {
        return RegisteredUser::orderBy('id', 'DESC')->get();
    }

    /**
     * Registrar un nuevo usuario de registro
     * Campos: name, phone, pin, estado, rol
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:registered_users,phone',
            'pin'   => 'required|digits:4',

            // nuevos campos
            'estado' => 'nullable|boolean',
            'rol'    => 'nullable|in:alimentador,parametros,mixto,celador',
        ]);

        // defaults
        $data['estado'] = $data['estado'] ?? 1;
        $data['rol']    = $data['rol']    ?? 'alimentador';

        $user = RegisteredUser::create($data);

        return response()->json([
            'message' => 'Usuario registrado correctamente',
            'user'    => $user,
        ], 201);
    }

    /**
     * Mostrar un usuario
     */
    public function show($id)
    {
        return RegisteredUser::findOrFail($id);
    }

    /**
     * Actualizar usuario
     */
    public function update(Request $request, $id)
    {
        $user = RegisteredUser::findOrFail($id);

        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:registered_users,phone,' . $user->id,
            'pin'   => 'required|digits:4',

            'estado' => 'nullable|boolean',
            'rol'    => 'nullable|in:alimentador,parametros,mixto,celador',
        ]);

        $user->update($data);

        return response()->json([
            'message' => 'Usuario actualizado',
            'user'    => $user,
        ], 200);
    }

    /**
     * Eliminar usuario (realmente inactivar)
     */
    public function destroy($id)
    {
        $user = RegisteredUser::findOrFail($id);

        $user->estado = 0;
        $user->save();

        return response()->json([
            'message' => 'Usuario inactivado',
        ], 200);
    }

    /**
     * Login simple por teléfono + PIN
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:20',
            'pin'   => 'required|digits:4',
        ]);

        $user = RegisteredUser::where('phone', $data['phone'])
            ->where('pin', $data['pin'])
            ->where('estado', 1) // Solo usuarios activos
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Credenciales inválidas',
            ], 401);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => $user,
        ], 200);
    }

    /**
     * Login por PIN solamente (para vista pública móvil).
     * Asume que el PIN es único entre usuarios activos.
     */
    public function loginByPin(Request $request)
    {
        $data = $request->validate([
            'pin' => 'required|digits:4',
        ]);

        $user = RegisteredUser::where('pin', $data['pin'])
            ->where('estado', 1) // Solo usuarios activos
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'PIN inválido o usuario inactivo',
            ], 401);
        }

        return response()->json([
            'message' => 'Login exitoso',
            'user'    => $user,
        ], 200);
    }
}
