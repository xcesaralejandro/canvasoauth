<?php

namespace xcesaralejandro\canvasoauth\Console\Commands;

use App\Models\CanvasClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CreateCanvasClient extends Command
{
  protected $signature = 'canvas:create-client';

  protected $description = 'Register a new Canvas OAuth client';

  public function handle(): int
  {
    $this->newLine();
    $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    $this->info('              Canvas OAuth Client Setup');
    $this->info('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
    $this->newLine();

    $this->line('<fg=gray>This command registers a Canvas OAuth client that can later be used to authenticate users or get access to the API resources.</>');

    $code = trim($this->ask(
      'Internal client code (used to identify this Canvas instance)'
    ));

    if ($code === '') {
      $this->error('The client code is required.');
      return self::FAILURE;
    }

    if (CanvasClient::query()->where('code', $code)->exists()) {
      $this->error("A client with the code '{$code}' already exists.");
      return self::FAILURE;
    }

    $url = rtrim(trim($this->ask(
      'Canvas base URL (e.g. https://example.instructure.com)'
    )), '/');

    if (! filter_var($url, FILTER_VALIDATE_URL)) {
      $this->error('Please enter a valid URL.');
      return self::FAILURE;
    }

    $clientId = trim($this->ask('Canvas Client ID'));

    if ($clientId === '') {
      $this->error('The Client ID is required.');
      return self::FAILURE;
    }

    $clientSecret = trim($this->ask('Canvas Client Secret'));

    if ($clientSecret === '') {
      $this->error('The Client Secret is required.');
      return self::FAILURE;
    }

    $this->newLine();
    $this->warn('Configuration Summary');

    $this->table(
      ['Property', 'Value'],
      [
        ['Code', mb_strtolower($code)],
        ['Canvas URL', $url],
        ['Client ID', $clientId],
        ['Client Secret', $clientSecret],
      ]
    );

    $this->newLine();

    if (! $this->confirm('Create this Canvas client?', true)) {
      $this->warn('Operation cancelled.');
      return self::SUCCESS;
    }

    $client = CanvasClient::query()->create([
      'code' => $code,
      'url' => $url,
      'client_id' => $clientId,
      'client_secret' => $clientSecret,
    ]);

    $this->newLine();
    $this->info('✓ Canvas client created successfully.');
    $this->line("<fg=green>Code:</> {$client->code}");
    $this->line("<fg=green>Authorization URL:</>");
    $this->line($client->getAuthorizationUrl());
    $this->newLine();
    return self::SUCCESS;
  }

  private function mask(string $value): string
  {
    if (strlen($value) <= 8) {
      return str_repeat('•', strlen($value));
    }

    return substr($value, 0, 4)
      . str_repeat('•', strlen($value) - 8)
      . substr($value, -4);
  }
}
