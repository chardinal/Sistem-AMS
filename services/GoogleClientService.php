<?php
// ============================================================
// GoogleClientService — Inisialisasi Google API Client
// Auto-refresh token jika expired
// ============================================================

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/google_config.php';

class GoogleClientService
{
    private Google\Client $client;

    /**
     * @throws Exception jika token belum ada atau tidak bisa direfresh
     */
    public function __construct()
    {
        $this->client = new Google\Client();
        
        // Baca credentials dari env var (production) atau file (local dev)
        $credJson = getenv('GOOGLE_CREDENTIALS_JSON');
        if ($credJson) {
            $this->client->setAuthConfig(json_decode($credJson, true));
        } else {
            if (!file_exists(GOOGLE_CREDENTIALS_PATH)) {
                throw new \RuntimeException('Google credentials tidak ditemukan. Set GOOGLE_CREDENTIALS_JSON di env.');
            }
            $this->client->setAuthConfig(GOOGLE_CREDENTIALS_PATH);
        }

        foreach (GOOGLE_SCOPES as $scope) {
            $this->client->addScope($scope);
        }

        // Baca token dari file (local dev) atau env var (production)
        $tokenJson = null;
        if (file_exists(GOOGLE_TOKEN_PATH)) {
            $tokenJson = file_get_contents(GOOGLE_TOKEN_PATH);
        } else {
            $tokenJson = getenv('GOOGLE_TOKEN_JSON') ?: null;
        }

        if (!$tokenJson) {
            throw new \RuntimeException(
                'Token Google belum ada. Buka /auth/google_auth.php untuk otorisasi.'
            );
        }

        $token = json_decode($tokenJson, true);
        $this->client->setAccessToken($token);

        // Auto-refresh jika token expired
        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if (!$refreshToken) {
                throw new \RuntimeException(
                    'Refresh token tidak tersedia. Ulangi otorisasi di /auth/google_auth.php.'
                );
            }
            $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
            $this->client->setAccessToken($newToken);
            
            // Simpan token baru ke file jika memungkinkan (ephemeral filesystem di Railway)
            if (is_writable(dirname(GOOGLE_TOKEN_PATH)) || (file_exists(GOOGLE_TOKEN_PATH) && is_writable(GOOGLE_TOKEN_PATH))) {
                @file_put_contents(GOOGLE_TOKEN_PATH, json_encode($this->client->getAccessToken(), JSON_PRETTY_PRINT));
            }
        }
    }

    public function getClient(): Google\Client
    {
        return $this->client;
    }

    /** Cek apakah token aktif tanpa throw exception */
    public static function isReady(): bool
    {
        if (file_exists(GOOGLE_TOKEN_PATH)) {
            $token = json_decode(file_get_contents(GOOGLE_TOKEN_PATH), true);
            return isset($token['access_token']);
        }
        
        $tokenJson = getenv('GOOGLE_TOKEN_JSON');
        if ($tokenJson) {
            $token = json_decode($tokenJson, true);
            return isset($token['access_token']);
        }
        
        return false;
    }
}
