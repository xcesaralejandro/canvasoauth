<?php

namespace xcesaralejandro\canvasoauth\Http\Controllers;

use App\Models\CanvasClient;
use App\Models\CanvasToken;
use App\Models\CanvasUser;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use xcesaralejandro\canvasoauth\DataStructures\AuthenticatedUser;
use xcesaralejandro\canvasoauth\Facades\CanvasOauth;

class CanvasOAuthController
{

    public function handleCallback(Request $request): mixed
    {
        Log::debug('[CanvasOAuthController] [handleCallback] Trying code exchange.', $request->all());
        try {
            if ($this->hasDeniedPermission($request)) {
                return $this->onPermissionDenied($request);
            }
            $canvas_client = CanvasClient::where('code', $request->state)->firstOrFail();
            $http_client = new Client();
            $params = [
                "client_id" => $canvas_client->client_id,
                "client_secret" => $canvas_client->client_secret,
                "code" => $request->code
            ];
            $options = ['form_params' => $params];
            $response = $http_client->request('POST', $canvas_client->getTokenUrl(), $options);
            $payload = json_decode($response->getBody()->getContents());
            Log::debug('[CanvasOAuthController] [handleCallback] Canvas response for code exchange.', [json_encode($payload)]);
            $authenticated_user = $this->createCanvasUser($payload, $canvas_client);
            $this->createCanvasToken($payload, $authenticated_user);
            return $this->onPermissionGranted($authenticated_user, $request);
        } catch (\Exception $e) {
            return $this->onError($e);
        }
    }

    protected function createCanvasUser(object $payload, CanvasClient $canvas_client): AuthenticatedUser
    {
        Log::debug('[CanvasOAuthController] [createCanvasUser] Filling authenticated user.');
        $user = CanvasUser::updateOrCreate(
            [
                'canvas_client_id' => $canvas_client->id,
                'canvas_id'        => $payload->user->id,
            ],
            [
                'canvas_global_id' => $payload->user->global_id,
                'name'             => $payload->user->name,
                'effective_locale' => $payload->user->effective_locale ?? null,
                'fake_student'     => $payload->user->fake_student ?? null,
            ]
        );
        $authenticated = new AuthenticatedUser();
        $authenticated->standard = $user;
        if (isset($payload->real_user)) {
            $real_user = CanvasUser::updateOrCreate(
                [
                    'canvas_client_id' => $canvas_client->id,
                    'canvas_id'        => $payload->real_user->id,
                ],
                [
                    'canvas_global_id' => $payload->real_user->global_id,
                    'name'             => $payload->real_user->name,
                    'effective_locale' => $payload->real_user->effective_locale ?? null,
                    'fake_student'     => $payload->real_user->fake_student ?? null,
                ]
            );
            $authenticated->supplanted_by = $real_user;
        }
        Log::debug('[CanvasOAuthController] [createCanvasUser] User fields was filled.');
        return $authenticated;
    }

    protected function createCanvasToken(object $payload, AuthenticatedUser $user): void
    {
        Log::debug('[CanvasOAuthController] [createCanvasToken] Triying save the token...');
        $fields = [
            'access_token' => $payload->access_token,
            'token_type' => $payload->token_type,
            'refresh_token' => $payload->refresh_token,
            'expires_in' => $payload->expires_in
        ];
        $conditions = ['canvas_user_id' => $user->standard->id];
        $result = CanvasToken::updateOrCreate($conditions, $fields);
        Log::debug('[CanvasOAuthController] [createCanvasToken] Token was created or update.', [$result]);
    }

    protected function hasDeniedPermission(Request $request)
    {
        return !isset($request->code);
    }

    public function onPermissionGranted(AuthenticatedUser $user, Request $request): mixed
    {
        Log::debug('[CanvasOAuthController] [onPermissionGranted] Token was stored successfully.', [json_encode($user), $request->all()]);
        return 'onPermissionGranted() method.';
    }

    public function onPermissionDenied(Request $request): mixed
    {
        Log::debug('[CanvasOAuthController] [onPermissionDenied] Permission rejected.', $request->all());
        return 'onPermissionDenied() method.';
    }

    public function onError(\Exception $exception): mixed
    {
        Log::error('[CanvasOAuthController] [onError] Something went wrong during the credential exchange', [$exception->getMessage()]);
        throw new \Exception($exception);
    }
}
