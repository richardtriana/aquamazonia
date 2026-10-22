<?php

namespace App\Console\Commands;

use App\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup:create
                            {--tipo=automatico : manual o automatico}
                            {--user= : nombre del usuario que lo genera}';

    protected $description = 'Genera un backup de la base de datos (rotación semanal)';

    private $dias = [
        'Monday'    => 'lunes',
        'Tuesday'   => 'martes',
        'Wednesday' => 'miercoles',
        'Thursday'  => 'jueves',
        'Friday'    => 'viernes',
        'Saturday'  => 'sabado',
        'Sunday'    => 'domingo',
    ];

    public function handle()
    {
        $tipo  = $this->option('tipo') ?? 'automatico';
        $user  = $this->option('user') ?? 'sistema';
        $diaNombre = $this->dias[date('l')] ?? strtolower(date('l'));
        $filename  = "backup_{$diaNombre}.sql";

        $db = config('database.connections.mysql');
        $host     = $db['host'];
        $port     = $db['port'];
        $database = $db['database'];
        $username = $db['username'];
        $password = $db['password'];

        $backupDir = storage_path('app/backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $filepath = $backupDir . DIRECTORY_SEPARATOR . $filename;

        // Eliminar el backup anterior del mismo día (rotación semanal)
        $anterior = Backup::where('dia_semana', $diaNombre)->first();
        if ($anterior) {
            if (file_exists($anterior->filepath)) {
                @unlink($anterior->filepath);
            }
            $anterior->delete();
        }

        // Buscar mysqldump: primero ruta XAMPP Windows, luego PATH
        $mysqldump = 'mysqldump';
        $candidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 8.0\\bin\\mysqldump.exe',
            'C:\\Program Files\\MySQL\\MySQL Server 5.7\\bin\\mysqldump.exe',
        ];
        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                $mysqldump = $candidate;
                break;
            }
        }

        $passwordFlag = $password ? '-p' . $password : '';
        $cmd = sprintf(
            '"%s" --host=%s --port=%s --user=%s %s --single-transaction --quick --lock-tables=false %s > %s 2>&1',
            $mysqldump,
            $host,
            $port,
            $username,
            $passwordFlag,
            $database,
            $filepath
        );

        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($filepath) || filesize($filepath) < 100) {
            $this->error('Error al generar el backup. Salida: ' . implode("\n", $output));
            return 1;
        }

        $size = filesize($filepath);

        Backup::create([
            'filename'   => $filename,
            'filepath'   => $filepath,
            'size'       => $size,
            'tipo'       => $tipo,
            'dia_semana' => $diaNombre,
            'creado_por' => $user,
        ]);

        $this->info("Backup creado: {$filename} ({$size} bytes)");
        return 0;
    }
}
