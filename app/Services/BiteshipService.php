<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BiteshipService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected ?string $originId;

    public function __construct()
    {
        $this->apiKey = config('biteship.api_key') ?? config('services.biteship.api_key') ?? '';
        $this->baseUrl = config('biteship.base_url') ?? config('services.biteship.base_url') ?? 'https://api.biteship.com';
        $this->originId = config('biteship.origin_id') ?? config('services.biteship.origin_id');
    }

    /**
     * Get active couriers list from Biteship
     */
    public function getCouriers(): array
    {
        $url = "{$this->baseUrl}/v1/couriers";
        
        Log::info('Biteship API GET Couriers Request:', [
            'url' => $url,
            'api_key' => $this->maskApiKey($this->apiKey)
        ]);

        if (empty($this->apiKey)) {
            Log::warning('Biteship API Key is empty.');
            return $this->getDefaultCouriers();
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json'
            ])->get($url);

            Log::info('Biteship API GET Couriers Response:', [
                'status_code' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json()['couriers'] ?? $this->getDefaultCouriers();
            }

            Log::error('Biteship GET Couriers failed: ' . $response->body());
            return $this->getDefaultCouriers();
        } catch (\Exception $e) {
            Log::error('Biteship GET Couriers exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return $this->getDefaultCouriers();
        }
    }

    public function calculateRates(array $origin, array $destination, array $items, string $couriers): array
    {
        $url = "{$this->baseUrl}/v1/rates/couriers";

        if (empty($this->apiKey)) {
            Log::warning('Biteship calculate rates requested, but API Key is empty.');
            return ['error' => 'API Key Biteship belum diisi. Silakan isi API Key di menu Pengaturan Pengiriman.'];
        }

        if (empty($origin['postal_code']) && empty($this->originId)) {
            Log::warning('Biteship calculate rates requested, but Origin ID & Postal Code are empty.');
            return ['error' => 'Origin ID atau Kode Pos Gudang asal kosong. Silakan lengkapi di menu Pengaturan.'];
        }

        try {
            $payload = [
                'origin_postal_code' => (int) ($origin['postal_code'] ?? 12140),
                'destination_postal_code' => (int) ($destination['postal_code'] ?? 0),
                'couriers' => $couriers,
                'items' => $items
            ];

            // Use origin area ID if available
            if (!empty($this->originId)) {
                $payload['origin_area_id'] = $this->originId;
            } elseif (!empty($origin['area_id'])) {
                $payload['origin_area_id'] = $origin['area_id'];
            }

            if (!empty($destination['area_id'])) {
                $payload['destination_area_id'] = $destination['area_id'];
            }

            if (!empty($origin['latitude']) && !empty($origin['longitude'])) {
                $payload['origin_latitude'] = (float) $origin['latitude'];
                $payload['origin_longitude'] = (float) $origin['longitude'];
            }
            if (!empty($destination['latitude']) && !empty($destination['longitude'])) {
                $payload['destination_latitude'] = (float) $destination['latitude'];
                $payload['destination_longitude'] = (float) $destination['longitude'];
            }

            $headers = [
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ];

            Log::info('Endpoint', ['url'=>$url]);
            Log::info('Headers', $headers);
            Log::info('Payload', $payload);

            $response = Http::withHeaders($headers)->post($url, $payload);

            Log::info('Status', ['status'=>$response->status()]);
            Log::info('Raw Response', ['body'=>$response->body()]);

            if ($response->successful()) {
                $pricing = $response->json()['pricing'] ?? [];
                $normalized = [];
                foreach ($pricing as $rate) {
                    $normalized[] = [
                        'company' => $rate['company'] ?? '',
                        'service' => $rate['courier_service_name'] ?? '',
                        'type'    => $rate['courier_service_code'] ?? '',
                        'price'   => (int) ($rate['price'] ?? 0),
                        'duration'=> $rate['duration'] ?? '',
                    ];
                }
                return $normalized;
            }

            return [
                'success' => false,
                'status' => $response->status(),
                'body' => $response->json(),
                'raw' => $response->body(),
            ];

        } catch (\Exception $e) {
            Log::error('Biteship calculate rates exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'status' => 500,
                'body' => ['message' => $e->getMessage()],
                'raw' => $e->getMessage()
            ];
        }
    }

    /**
     * Create shipping order (shipment) via Biteship
     */
    public function createShipment(array $shipmentData): array
    {
        $url = "{$this->baseUrl}/v1/orders";

        Log::info('Biteship API Create Shipment Request:', [
            'url' => $url,
            'payload' => $shipmentData,
            'api_key' => $this->maskApiKey($this->apiKey)
        ]);

        if (empty($this->apiKey)) {
            Log::warning('Biteship API Key is empty.');
            return ['success' => false, 'message' => 'API Key Biteship tidak terkonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])->post($url, $shipmentData);

            Log::info('Biteship API Create Shipment Response:', [
                'status_code' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal membuat pengiriman ke Biteship.'
            ];
        } catch (\Exception $e) {
            Log::error('Biteship create shipment exception:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghubungi API Biteship.'
            ];
        }
    }

    /**
     * Track waybill shipment status
     */
    public function trackShipment(string $waybill, string $courierCode): array
    {
        $url = "{$this->baseUrl}/v1/trackings/{$waybill}/couriers/{$courierCode}";

        Log::info('Biteship API Track Shipment Request:', [
            'url' => $url,
            'api_key' => $this->maskApiKey($this->apiKey)
        ]);

        if (empty($this->apiKey)) {
            Log::warning('Biteship API Key is empty.');
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json'
            ])->get($url);

            Log::info('Biteship API Track Shipment Response:', [
                'status_code' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return $response->json() ?? [];
            }

            Log::error("Biteship track shipment {$waybill} failed: " . $response->body());
            return [];
        } catch (\Exception $e) {
            Log::error("Biteship track shipment exception:", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [];
        }
    }

    /**
     * Cancel shipping order (shipment)
     */
    public function cancelShipment(string $biteshipOrderId): array
    {
        $url = "{$this->baseUrl}/v1/orders/{$biteshipOrderId}";

        Log::info('Biteship API Cancel Shipment Request:', [
            'url' => $url,
            'api_key' => $this->maskApiKey($this->apiKey)
        ]);

        if (empty($this->apiKey)) {
            Log::warning('Biteship API Key is empty.');
            return ['success' => false, 'message' => 'API Key tidak dikonfigurasi.'];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $this->apiKey,
                'Accept' => 'application/json'
            ])->delete($url);

            Log::info('Biteship API Cancel Shipment Response:', [
                'status_code' => $response->status(),
                'body' => $response->json()
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json()
                ];
            }

            return [
                'success' => false,
                'message' => $response->json()['message'] ?? 'Gagal membatalkan pengiriman.'
            ];
        } catch (\Exception $e) {
            Log::error("Biteship cancel shipment exception:", [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return [
                'success' => false,
                'message' => 'Kesalahan koneksi ke Biteship.'
            ];
        }
    }

    /**
     * Mask API key for secure logging
     */
    protected function maskApiKey(?string $key): string
    {
        if (empty($key)) return 'NOT_SET';
        if (strlen($key) <= 12) return '***';
        return substr($key, 0, 6) . '...' . substr($key, -6);
    }

    /**
     * Default fallback couriers list in case of API failure
     */
    protected function getDefaultCouriers(): array
    {
        return [
            ['code' => 'jne', 'name' => 'JNE', 'description' => 'Jalur Nugraha Ekakurir', 'available_services' => ['reg', 'yes', 'oke']],
            ['code' => 'jnt', 'name' => 'J&T', 'description' => 'J&T Express', 'available_services' => ['ez', 'eco']],
            ['code' => 'sicepat', 'name' => 'SiCepat', 'description' => 'SiCepat Express', 'available_services' => ['reg', 'best', 'gokil']],
            ['code' => 'anteraja', 'name' => 'AnterAja', 'description' => 'AnterAja', 'available_services' => ['reg', 'nd']],
            ['code' => 'pos', 'name' => 'POS Indonesia', 'description' => 'POS Indonesia', 'available_services' => ['pos_reg', 'pos_next_day']],
            ['code' => 'tiki', 'name' => 'TIKI', 'description' => 'TIKI', 'available_services' => ['reg', 'ons', 'eco']],
            ['code' => 'ninja', 'name' => 'Ninja Xpress', 'description' => 'Ninja Express', 'available_services' => ['reg']],
            ['code' => 'lion', 'name' => 'Lion Parcel', 'description' => 'Lion Parcel', 'available_services' => ['reg', 'onepack']],
            ['code' => 'sap', 'name' => 'SAP Express', 'description' => 'SAP Express', 'available_services' => ['reg']],
            ['code' => 'ide', 'name' => 'ID Express', 'description' => 'ID Express', 'available_services' => ['reg', 'lite']],
        ];
    }
}
