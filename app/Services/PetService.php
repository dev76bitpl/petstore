<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use App\DTO\PetDTO;

class PetService
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.petstore.base_url'),
            'verify' => false, // ❗ nadal wyłączone sprawdzanie SSL
        ]);
    }

    /**
     * Make a request to the API.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     * @return array|null
     */
    private function requestApi($method, $uri, $options = [])
    {
        try {
            Log::info("[API REQUEST] {$method} {$uri}", $options);

            $response = $this->client->request($method, $uri, $options);
            $responseBody = $response->getBody()->getContents();

            Log::info("[API RESPONSE] {$method} {$uri} - Status: " . $response->getStatusCode(), [
                'body' => json_decode($responseBody, true) ?? $responseBody
            ]);

            return json_decode($responseBody, true);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                Log::warning("[API ERROR] Resource not found: {$uri}");
                return null;
            }
            Log::error("[API ERROR] Request failed: " . $e->getMessage());
            return null;
        } catch (\Exception $e) {
            Log::error("[API ERROR] Unexpected error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get the pets from the API.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getAllPets($limit = 10, $page = 1): array
    {
        $response = $this->requestApi('GET', 'pet/findByStatus', ['query' => ['status' => 'available']]);

        if (!is_array($response)) {
            return [
                'data' => [],
                'total' => 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'lastPage' => 1
            ];
        }

        $totalPets = count($response);
        $offset = ($page - 1) * $limit;
        $paginatedPets = array_slice($response, $offset, $limit);

        return [
            'data' => PetDTO::collection($paginatedPets),
            'total' => $totalPets,
            'perPage' => $limit,
            'currentPage' => $page,
            'lastPage' => max(1, ceil($totalPets / $limit))
        ];
    }

    /**
     * Get the pets by their IDs.
     *
     * @param array $ids
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getAllPetsFromAPI($limit = 10, $page = 1)
    {
        try {
            $response = $this->client->get('pet/findByStatus', [
                'query' => ['status' => 'available']
            ]);

            $pets = json_decode($response->getBody()->getContents(), true);

            Log::info("[API RESPONSE] Total pets fetched: " . count($pets));

            if (empty($pets)) {
                return [
                    'data' => [],
                    'total' => 0,
                    'perPage' => $limit,
                    'currentPage' => $page,
                    'lastPage' => 1
                ];
            }

            $totalPets = count($pets);
            $offset = ($page - 1) * $limit;
            $paginatedPets = array_slice($pets, $offset, $limit);

            return [
                'data' => $paginatedPets,
                'total' => $totalPets,
                'perPage' => $limit,
                'currentPage' => $page,
                'lastPage' => max(1, ceil($totalPets / $limit))
            ];
        } catch (\Exception $e) {
            Log::error("[API ERROR] Error fetching all pets: " . $e->getMessage());
            return [
                'data' => [],
                'total' => 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'lastPage' => 1
            ];
        }
    }

    /**
     * Get pets by their IDs.
     *
     * @param array $ids
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getPetsByIds(array $ids, $limit = 10, $page = 1)
    {
        $pets = [];

        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                Log::warning("[WARNING] Skipping invalid pet ID: {$id}");
                continue;
            }

            $response = $this->requestApi('GET', "pet/{$id}");

            if (!empty($response)) {
                $pets[] = new PetDTO($response);
            }
        }

        $totalPets = count($pets);
        $offset = ($page - 1) * $limit;
        $paginatedPets = array_slice($pets, $offset, $limit);

        return [
            'data' => $paginatedPets,
            'total' => $totalPets,
            'perPage' => $limit,
            'currentPage' => $page,
            'lastPage' => max(1, ceil($totalPets / $limit))
        ];
    }

    /**
     * Get a pet by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getPetById($id): ?PetDTO
    {
        $data = $this->requestApi('GET', "pet/{$id}");

        return $data ? new PetDTO($data) : null;
    }

    /**
     * Add a new pet to the API.
     *
     * @param array $data
     * @return array
     */
    public function addPet($data)
    {
        $payload = [
            'id' => rand(111111111111111111, 999999999999999999),
            'category' => [
                'id' => $data['category_id'] ?? rand(1, 10),
                'name' => $data['category_name'] ?? 'General'
            ],
            'name' => $data['name'],
            'photoUrls' => $data['photoUrls'] ?? ["https://example.com/default.jpg"],
            'tags' => $data['tags'] ?? [['id' => rand(1, 10), 'name' => 'friendly']],
            'status' => $data['status'],
        ];

        Log::info('Adding pet with data:', $payload);

        return $this->requestApi('POST', 'pet', ['json' => $payload]);
    }

    /**
     * Update an existing pet in the API.
     *
     * @param int $id
     * @param array $data
     * @return void
     */
    public function updatePet($id, $data)
    {
        $this->requestApi('PUT', 'pet', ['json' => array_merge(['id' => $id], $data)]);
    }


    /**
     * Delete a pet from the API.
     *
     * @param int $id
     * @return void
     */
    public function deletePet($id)
    {
        $this->requestApi('DELETE', "pet/{$id}");
    }
}
