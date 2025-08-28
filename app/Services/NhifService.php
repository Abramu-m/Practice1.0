<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\RequestException;

class NhifService
{
    protected $config;
    protected $username;
    protected $password;
    protected $mode;
    protected $baseUrl;
    protected $token;
    protected $tokenExpiresAt;
    protected $tokenFingerprint;

    public function __construct()
    {
        $this->config = config('nhif');
        $this->username = $this->config['credentials']['username'];
        $this->password = $this->config['credentials']['password'];
        $this->mode = $this->config['mode'];
        $this->baseUrl = $this->config['url'][$this->mode];
    }

    /**
     * Obtain an access token from the NHIF token endpoint and cache it in memory
     * Returns header string like "Bearer <token>" or null on failure
     */
    private function obtainToken(): ?string
    {
        try {
            $tokenUrl = $this->config['url']['token'][$this->mode] ?? ($this->config['url']['token'] ?? null);
            if (!$tokenUrl) {
                Log::error('NHIF token URL not configured');
                return null;
            }

            $response = Http::timeout($this->config['timeout'])->asForm()->post($tokenUrl, [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if (! $response->successful()) {
                Log::error('NHIF token request failed', ['url' => $tokenUrl, 'status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            // normalize token type to 'Bearer' (some servers require capitalized scheme)
            $rawTokenType = $data['token_type'] ?? ($data['tokenType'] ?? 'Bearer');
            $tokenType = ucfirst(strtolower(trim($rawTokenType)));
            $accessToken = $data['access_token'] ?? ($data['accessToken'] ?? null);

            if (! $accessToken) {
                Log::error('NHIF token response missing access_token: ' . json_encode($data));
                return null;
            }

            // store full header value but avoid logging the actual token
            $this->token = trim($tokenType . ' ' . $accessToken);
            // store a short fingerprint of the raw access token for diagnostics
            $this->tokenFingerprint = substr(sha1($accessToken), 0, 12);
            $expiresIn = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
            $this->tokenExpiresAt = now()->addSeconds($expiresIn - 30); // small buffer

            Log::info('NHIF token obtained', ['token_type' => $tokenType, 'expires_in' => $expiresIn]);

            return $this->token;
        } catch (RequestException $e) {
            Log::error('NHIF Token Request Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtain token from a specific URL (used when tariffs require a different token endpoint)
     */
    private function obtainTokenFromUrl(string $tokenUrl): ?string
    {
        try {
            $response = Http::timeout($this->config['timeout'])->asForm()->post($tokenUrl, [
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password,
            ]);

            if (! $response->successful()) {
                Log::error('NHIF token request failed (custom url)', ['url' => $tokenUrl, 'status' => $response->status(), 'body' => $response->body()]);
                return null;
            }

            $data = $response->json();
            $rawTokenType = $data['token_type'] ?? ($data['tokenType'] ?? 'Bearer');
            $tokenType = ucfirst(strtolower(trim($rawTokenType)));
            $accessToken = $data['access_token'] ?? ($data['accessToken'] ?? null);

            if (! $accessToken) {
                Log::error('NHIF token response missing access_token (custom url): ' . json_encode($data));
                return null;
            }

            $this->token = trim($tokenType . ' ' . $accessToken);
            $this->tokenFingerprint = substr(sha1($accessToken), 0, 12);
            $expiresIn = isset($data['expires_in']) ? (int)$data['expires_in'] : 3600;
            $this->tokenExpiresAt = now()->addSeconds($expiresIn - 30);

            Log::info('NHIF token obtained (custom url)', ['token_type' => $tokenType, 'expires_in' => $expiresIn, 'url' => $tokenUrl]);

            return $this->token;
        } catch (RequestException $e) {
            Log::error('NHIF Token Request Error (custom url): ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Choose an appropriate token endpoint for tariffs. If the configured tariffs URL
     * points at the claimsserver, prefer the production token endpoint (legacy behaviour).
     */
    private function obtainTokenForTariffs(string $configuredTariffsUrl): ?string
    {
        // if tariffs configured to use claimsserver, prefer production token url when available
        if (stripos($configuredTariffsUrl, 'claimsserver') !== false && isset($this->config['url']['token']['production'])) {
            $tokenUrl = $this->config['url']['token']['production'];
            Log::info('NHIF obtaining token for tariffs using claimsserver token endpoint', ['token_url' => $tokenUrl]);
            return $this->obtainTokenFromUrl($tokenUrl);
        }

        // default behaviour
        return $this->obtainToken();
    }

    /**
     * Return an Authorization header value. Attempts to reuse cached token and falls back to Basic auth.
     */
    private function getAuthHeader(): string
    {
        if (!empty($this->token) && !empty($this->tokenExpiresAt) && now()->lt($this->tokenExpiresAt)) {
            Log::info('NHIF getAuthHeader: using cached token', ['expires_at' => $this->tokenExpiresAt->toDateTimeString()]);
            return $this->token;
        }

        $token = $this->obtainToken();
        if ($token) {
            Log::info('NHIF getAuthHeader: using newly obtained token', ['header_scheme' => explode(' ', $token)[0] ?? null]);
            return $token;
        }

        // Fallback to Basic auth header if token cannot be obtained
        Log::warning('NHIF getAuthHeader: falling back to Basic auth');
        return 'Basic ' . base64_encode($this->username . ':' . $this->password);
    }

    /**
     * Verify NHIF member
     */
    public function verifyMember(string $cardNumber, int $visitTypeId = 1, string $referralNumber = '', string $remarks = 'verification')
    {
        try {
            $response = Http::timeout($this->config['timeout'])
                ->withBasicAuth($this->username, $this->password)
                ->post($this->config['url']['member_verification'][$this->mode], [
                    'CardNo' => $cardNumber,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to verify member',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Member Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during member verification',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get card details
     */
    public function getCardDetails(string $cardNumber)
    {
        try {
            $url = $this->config['url']['verification'][$this->mode] . '?CardNo=' . urlencode($cardNumber);
            $authHeader = $this->getAuthHeader();
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders(['Authorization' => $authHeader])
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get card details',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Card Details Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during card details retrieval',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download tariffs without excluded services
     */
    public function downloadTariffsWithoutExcludedService(string $facilityCode)
    {
        try {
            // Ensure we have a token first — tariffs endpoint requires a valid access token
            $token = $this->obtainTokenForTariffs($this->config['url']['tariffs']);
            if (! $token) {
                Log::error('NHIF Tariffs download aborted: unable to obtain access token');
                return [
                    'success' => false,
                    'message' => 'Failed to download tariffs: unable to obtain access token',
                    'error' => 'No access token available',
                ];
            }

            // The legacy client sometimes stores the full pricelist URL (including GetPricePackage...)
            $configured = rtrim($this->config['url']['tariffs'], '/');
            if (stripos($configured, 'GetPricePackage') !== false) {
                // configured value already contains the full GetPricePackage path
                $url = $configured . (strpos($configured, '?') === false ? '?' : '&') . 'FacilityCode=' . urlencode($facilityCode);
            } else {
                // configured is a base Packages/ URL — append the GetPricePackage path
                $url = $configured . '/GetPricePackage?FacilityCode=' . urlencode($facilityCode);
            }
            Log::info('NHIF Tariffs download URL: ' . $url, ['configured_tariffs' => $this->config['url']['tariffs']]);
            // Log a short, non-sensitive sample of the Authorization header and token fingerprint for debugging
            try {
                $authSample = substr($token, 0, 24);
            } catch (\Exception $e) {
                $authSample = null;
            }
            Log::info('NHIF Tariffs request headers sample', ['auth_header_sample' => $authSample, 'token_fp' => $this->tokenFingerprint ?? null]);

            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'Authorization' => $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ])
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('NHIF Tariffs download failed', ['url' => $url, 'status' => $response->status(), 'body' => $response->body(), 'response_headers' => $response->headers(), 'token_fp' => $this->tokenFingerprint ?? null]);

            return [
                'success' => false,
                'message' => 'Failed to download tariffs',
                'error' => $response->body(),
                'url' => $url,
                'status' => $response->status(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Tariffs Download Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during tariffs download',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Download tariffs with excluded services
     */
    public function downloadTariffsWithExcludedService(string $facilityCode)
    {
        try {
            // Ensure we have a token first — tariffs endpoint requires a valid access token
            $token = $this->obtainTokenForTariffs($this->config['url']['tariffs']);
            if (! $token) {
                Log::error('NHIF Tariffs (with excluded) download aborted: unable to obtain access token');
                return [
                    'success' => false,
                    'message' => 'Failed to download tariffs with excluded services: unable to obtain access token',
                    'error' => 'No access token available',
                ];
            }

            $configured = rtrim($this->config['url']['tariffs'], '/');
            if (stripos($configured, 'GetPricePackage') !== false) {
                $url = $configured . (strpos($configured, '?') === false ? '?' : '&') . 'FacilityCode=' . urlencode($facilityCode);
            } else {
                $url = $configured . '/GetPricePackageWithExcludedServices?FacilityCode=' . urlencode($facilityCode);
            }
            Log::info('NHIF Tariffs (with excluded) download URL: ' . $url, ['configured_tariffs' => $this->config['url']['tariffs']]);

            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'Authorization' => $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ])
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            Log::error('NHIF Tariffs (with excluded) download failed', ['url' => $url, 'status' => $response->status(), 'body' => $response->body(), 'response_headers' => $response->headers(), 'token_fp' => $this->tokenFingerprint ?? null]);

            return [
                'success' => false,
                'message' => 'Failed to download tariffs with excluded services',
                'error' => $response->body(),
                'url' => $url,
                'status' => $response->status(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Tariffs With Excluded Services Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during tariffs download',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Submit claim to NHIF
     */
    public function submitClaimToNHIF(array $claimData)
    {
        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders(['Authorization' => $this->getAuthHeader()])
                ->post($this->config['url']['claim'], $claimData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to submit claim',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Claim Submission Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during claim submission',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get submitted claims
     */
    public function getSubmittedClaims(string $facilityCode, int $claimYear, int $claimMonth)
    {
        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders(['Authorization' => $this->getAuthHeader()])
                ->post($this->config['url']['claim_submitted'], [
                    'FacilityCode' => $facilityCode,
                    'ClaimYear' => $claimYear,
                    'ClaimMonth' => $claimMonth,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to get submitted claims',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Get Submitted Claims Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during submitted claims retrieval',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Authorize card (request NHIF to authorize beneficiary for service)
     */
    public function authorizeCard(string $cardNumber, int $visitTypeId = 1, string $referralNumber = '', string $remarks = 'authorization')
    {
        try {
            $url = $this->config['url']['authorize'][$this->mode] ?? ($this->config['url']['authorize'] ?? null);
            if (! $url) {
                Log::error('NHIF authorize URL not configured');
                return [ 'success' => false, 'message' => 'Authorize URL not configured' ];
            }

            $authHeader = $this->getAuthHeader();

            $payload = [
                'CardNo' => $cardNumber,
                'VisitTypeID' => $visitTypeId,
                'ReferralNo' => $referralNumber ?? '',
                'Remarks' => $remarks ?? '',
            ];

            $response = Http::timeout($this->config['timeout'])
                ->withHeaders([
                    'Authorization' => $authHeader,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json; charset=utf-8',
                ])
                ->retry($this->config['retry_attempts'] ?? 2, 100)
                ->post($url, $payload);

            if ($response->successful()) {
                return [ 'success' => true, 'data' => $response->json() ];
            }

            Log::warning('NHIF authorize failed', ['url' => $url, 'status' => $response->status(), 'body' => $response->body()]);
            return [ 'success' => false, 'message' => 'Failed to authorize card', 'error' => $response->body() ];

        } catch (RequestException $e) {
            Log::error('NHIF Authorize Error: ' . $e->getMessage());
            return [ 'success' => false, 'message' => 'Connection error during authorization', 'error' => $e->getMessage() ];
        }
    }

    /**
     * Submit referral to NHIF
     */
    public function submitReferralToNHIF(array $referralData)
    {
        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders(['Authorization' => $this->getAuthHeader()])
                ->post($this->config['url']['referral'], $referralData);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to submit referral',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Referral Submission Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during referral submission',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Verify pre-approved services
     */
    public function verifyPreApprovedService(string $cardNumber, string $referenceNumber, string $itemCode)
    {
        try {
            $response = Http::timeout($this->config['timeout'])
                ->withHeaders(['Authorization' => $this->getAuthHeader()])
                ->post($this->config['url']['pre_approved'], [
                    'CardNo' => $cardNumber,
                    'ReferenceNo' => $referenceNumber,
                    'ItemCode' => $itemCode,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->body(), // Returns "VALID" or "INVALID"
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to verify pre-approved service',
                'error' => $response->body(),
            ];
        } catch (RequestException $e) {
            Log::error('NHIF Pre-approved Service Verification Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Connection error during pre-approved service verification',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Synchronize tariffs from NHIF API
     */
    public function syncTariffs(string $facilityCode = null): array
    {
        try {
            $facilityCode = $facilityCode ?? ($this->config['facility_code'] ?? null) ?? config('nhif.facility_code');
            if (! $facilityCode) {
                return ['success' => false, 'message' => 'Facility code not configured'];
            }

            // Try to fetch tariffs from NHIF
            $resp = $this->downloadTariffsWithoutExcludedService($facilityCode);
            if (! $resp['success']) {
                Log::error('NHIF syncTariffs: failed to download tariffs', ['error' => $resp['error'] ?? null]);
                return ['success' => false, 'message' => 'Failed to download tariffs', 'error' => $resp['error'] ?? null];
            }

            $items = $resp['data'] ?? [];
            if (! is_array($items) || empty($items)) {
                return ['success' => true, 'message' => 'Successfully synced 0 tariff items', 'synced_count' => 0];
            }

            $count = 0;
            foreach ($items as $item) {
                // some fields may be nested or different cased; normalize keys
                $code = $item['ItemCode'] ?? $item['itemCode'] ?? $item['ItemCode'];
                if (! $code) continue;

                $scheme = $item['SchemeID'] ?? $item['schemeID'] ?? $item['SchemeID'] ?? null;
                $package = $item['PackageID'] ?? $item['packageID'] ?? null;
                $name = $item['ItemName'] ?? $item['itemName'] ?? null;
                $price = isset($item['UnitPrice']) ? $item['UnitPrice'] : (isset($item['unitPrice']) ? $item['unitPrice'] : 0);
                $isRestricted = isset($item['IsRestricted']) ? (bool)$item['IsRestricted'] : (isset($item['isRestricted']) ? (bool)$item['isRestricted'] : false);
                $excludedRaw = $item['ExcludedForProducts'] ?? $item['excluded_for_products'] ?? null;

                // parse excluded products list if present (format: CODE~NAME,CODE~NAME,...)
                $excludedArr = null;
                if (is_string($excludedRaw) && strlen(trim($excludedRaw)) > 0) {
                    $parts = array_filter(array_map('trim', explode(',', $excludedRaw)));
                    $codes = [];
                    foreach ($parts as $p) {
                        $seg = explode('~', $p);
                        if (count($seg) > 0 && strlen($seg[0]) > 0) $codes[] = $seg[0];
                    }
                    $excludedArr = $codes ?: null;
                }

                // Use NULL for scheme_id when not provided so unique key semantics remain correct
                \App\Models\NhifTariff::updateOrCreate(
                    [
                        'facility_code' => $facilityCode,
                        'item_code' => $code,
                        // preserve NULL instead of coercing to 0
                        'scheme_id' => $scheme ?? null,
                    ],
                    [
                        'item_name' => $name,
                        'unit_price' => $price,
                        'package_id' => $package,
                        'is_restricted' => $isRestricted,
                        'is_excluded' => ! empty($excludedArr),
                        'excluded_for_products' => $excludedArr,
                        // let Eloquent handle created_at/updated_at timestamps
                        'last_updated' => now(),
                    ]
                );
                $count++;
            }

            Log::info("NHIF Tariffs synced successfully. Count: {$count}");

            return ['success' => true, 'message' => "Successfully synced {$count} tariff items", 'synced_count' => $count];

        } catch (\Exception $e) {
            Log::error('NHIF Tariffs Sync Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred while syncing tariffs: ' . $e->getMessage()];
        }
    }
}
