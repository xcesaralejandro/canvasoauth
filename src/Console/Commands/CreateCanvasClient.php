<?php

namespace xcesaralejandro\canvasoauth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCanvasClient extends Command
{

  protected $signature = 'canvas:create-client';

  protected $description = 'Crea un nuevo registro en la tabla canvas_clients mediante preguntas interactivas';

  public function handle()
  {
    $this->info('--- Creación de Cliente Canvas ---');

    // 1. Preguntar por el código interno con validación básica
    $code = $this->ask('Código interno para identificar y recuperar el cliente posteriormente dentro de tu APP.');
    if (empty($code)) {
      $this->error('El código interno es obligatorio.');
      return 1;
    }

    // Verificar si ya existe para evitar errores de clave única
    $exists = DB::table('canvas_clients')->where('code', $code)->exists();
    if ($exists) {
      $this->error("El código '{$code}' ya está registrado.");
      return 1;
    }

    // 2. Preguntar por la URL
    $url = $this->ask('URL del dominio de canvas');
    if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
      $this->error('Debes ingresar una URL válida.');
      return 1;
    }

    $clientId = $this->ask('Client ID');
    $clientSecret = $this->ask('Client Secret');

    if ($this->confirm("¿Estás seguro de que deseas guardar al cliente '{$code}'?", true)) {
      DB::table('canvas_clients')->insert([
        'code' => $code,
        'url' => $url,
        'client_id' => $clientId,
        'client_secret' => $clientSecret,
        'created_at' => now(),
        'updated_at' => now(),
      ]);
      $this->info('¡Cliente Canvas creado con éxito!');
    } else {
      $this->warn('Operación cancelada.');
    }
    return 0;
  }
}
