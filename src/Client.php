<?php

namespace Logicly\EasyOAuthClient;

use Exception;
use Illuminate\Support\Facades\Http;

class Client
{
    private array $providers;

    public function __construct(
        private ?string $provider = null,
        private ?string $code = null,
        private ?string $refresh_token = null,
        private ?string $access_token = null,
        private ?string $info_body = null,
    )
    {
        $this->providers = config('easy-oauth-client');

        $this->providers = array_change_key_case($this->providers);
        $this->provider = $provider ? strtolower($provider) : null;
    }

    public function setProvider($provider): self
    {
        $this->provider = strtolower($provider);

        return $this;
    }

    public function setCode($code): self
    {
        $this->code = $code;

        return $this;
    }

    public function setRedirectUri($redirect_uri, $provider = null): self
    {
        $provider = $provider ?? $this->provider;
        $this->providers[$provider]['redirect_uri'] = $redirect_uri;

        return $this;
    }

    public function setRefreshToken($refresh_token): self
    {
        $this->refresh_token = $refresh_token;

        return $this;
    }

    public function setAccessToken($access_token): self
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getToken($code = null): array
    {
        $this->code = $code ?? $this->code;

        if (! $this->code) {
            throw new Exception('No code set');
        } elseif (! $this->provider) {
            throw new Exception('No provider set');
        }

        return $this->tokenHandler(Token::ACCESS_TOKEN);
    }

    public function refreshToken($refresh_token = null)
    {
        $this->refresh_token = $refresh_token ?? $this->refresh_token;

        if (! $this->refresh_token) {
            throw new Exception('No refresh token set');
        } elseif (! $this->provider) {
            throw new Exception('No provider set');
        }

        return $this->tokenHandler(Token::REFRESH_TOKEN);
    }

    public function getInfo($access_token = null, $body = null)
    {
        $this->info_body = $body ?? $this->info_body ?? null;
        $this->access_token = $access_token ?? $this->access_token;

        if (! $this->access_token) {
            throw new Exception('No access token set');
        } elseif (! $this->provider) {
            throw new Exception('No provider set');
        }

        return $this->infoHandler();
    }

    private function tokenHandler(Token $type): ?array
    {
        $body = [
            'client_id' => $this->providers[$this->provider]['client_id'],
            'client_secret' => $this->providers[$this->provider]['client_secret'],
            'redirect_uri' => $this->providers[$this->provider]['redirect_uri'],
            'grant_type' => $this->providers[$this->provider][$type->value]['grant_type'],
        ];

        if ($type === Token::ACCESS_TOKEN) {
            $body['code'] = $this->code;
        } elseif ($type === Token::REFRESH_TOKEN) {
            $body['refresh_token'] = $this->refresh_token;
        }

        $token = [];

        switch ($this->providers[$this->provider][$type->value]['auth']) {
            case 'body':
                if ($this->providers[$this->provider][$type->value]['method'] === 'POST') {
                    $token = Http::asForm()
                        ->post($this->providers[$this->provider][$type->value]['url'], $body)
                        ->json();
                } else {
                    $token = Http::get($this->providers[$this->provider][$type->value]['url'], $body)
                        ->json();
                }
                break;
            case 'basic':
                if ($this->providers[$this->provider][$type->value]['method'] === 'POST') {
                    $token = Http::withBasicAuth($this->providers[$this->provider]['client_id'], $this->providers[$this->provider]['client_secret'])
                        ->asForm()
                        ->post($this->providers[$this->provider][$type->value]['url'], $body)
                        ->json();
                } else {
                    $token = Http::withBasicAuth($this->providers[$this->provider]['client_id'], $this->providers[$this->provider]['client_secret'])
                        ->get($this->providers[$this->provider][$type->value]['url'], $body)
                        ->json();
                }
        }

        $fields = $token;

        if (is_array($this->providers[$this->provider][$type->value]['fields'])) {
            foreach ($this->providers[$this->provider][$type->value]['fields'] as $key => $field) {
                if (isset($token[$key])) {
                    $fields[$field] = $token[$key];
                }
            }
        }

        return $fields ?? null;
    }

    private function infoHandler(): ?array
    {
        if ($this->providers[$this->provider]['info']['method'] === 'POST') {
            $info = Http::withToken($this->access_token)->asForm()
                ->post($this->providers[$this->provider]['info']['url'], $this->info_body)
                ->json();
        } else {
            $info = Http::withToken($this->access_token)
                ->get($this->providers[$this->provider]['info']['url'], $this->info_body)
                ->json();
        }

        $fields = [];

        foreach ($this->providers[$this->provider]['info']['fields'] as $field) {
            $fields[$field] = $info[$field];
        }

        return $fields ?? null;
    }
}
