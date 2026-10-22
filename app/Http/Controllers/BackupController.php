<?php

namespace App\Http\Controllers;

use App\Backup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class BackupController extends Controller
{
    public function index()
    {
        $backups = Backup::orderBy('created_at', 'desc')->get()->map(function ($b) {
            return [
                'id'          => $b->id,
                'filename'    => $b->filename,
                'size'        => $b->size,
                'size_fmt'    => $b->size_formatted,
                'tipo'        => $b->tipo,
                'dia_semana'  => $b->dia_semana,
                'creado_por'  => $b->creado_por,
                'created_at'  => $b->created_at->format('Y-m-d H:i:s'),
                'existe'      => file_exists($b->filepath),
            ];
        });

        return response()->json($backups);
    }

    public function store(Request $request)
    {
        $authUser = auth('web')->user() ?? auth()->user();
        $user = $authUser ? ($authUser->name ?? $authUser->email ?? 'admin') : 'admin';

        Artisan::call('backup:create', [
            '--tipo' => 'manual',
            '--user' => $user,
        ]);

        $output = Artisan::output();
        $ultimo = Backup::orderBy('created_at', 'desc')->first();

        return response()->json([
            'message' => 'Backup creado correctamente',
            'output'  => trim($output),
            'backup'  => $ultimo ? [
                'id'         => $ultimo->id,
                'filename'   => $ultimo->filename,
                'size_fmt'   => $ultimo->size_formatted,
                'tipo'       => $ultimo->tipo,
                'dia_semana' => $ultimo->dia_semana,
                'creado_por' => $ultimo->creado_por,
                'created_at' => $ultimo->created_at->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }

    public function download($id)
    {
        $backup = Backup::findOrFail($id);

        if (!file_exists($backup->filepath)) {
            return response()->json(['error' => 'El archivo no existe en el servidor'], 404);
        }

        return response()->download($backup->filepath, $backup->filename, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function destroy($id)
    {
        $backup = Backup::findOrFail($id);

        if (file_exists($backup->filepath)) {
            @unlink($backup->filepath);
        }

        $backup->delete();

        return response()->json(['message' => 'Backup eliminado']);
    }
}
