<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class PetService
{
    protected $client;
    protected $baseUrl = 'https://petstore.swagger.io/v2/
';

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false, // ❗ Wyłącza sprawdzanie SSL
        ]);
    }

    /**
     * Get the pets from the API.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getAllPets($limit = 10, $page = 1)
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
     * Get the pets by their IDs.
     *
     * @param array $ids
     * @param int $limit
     * @param int $page
     * @return array
     */
    public function getPetsByIds(array $ids, $limit = 10, $page = 1)
    {
        $pets = [];

        if (empty($ids)) {
            Log::warning("[WARNING] No pet IDs provided to fetch.");
            return [
                'data' => [],
                'total' => 0,
                'perPage' => $limit,
                'currentPage' => $page,
                'lastPage' => 1
            ];
        }

        foreach ($ids as $id) {
            if (!is_numeric($id)) {
                Log::warning("[WARNING] Skipping invalid pet ID: {$id}");
                continue;
            }

            try {
                Log::info("[API REQUEST] Fetching pet ID: {$id}");
                $response = $this->client->get("pet/{$id}");
                $pet = json_decode($response->getBody()->getContents(), true);

                if (!empty($pet)) {
                    $pets[] = $pet;
                }
            } catch (\Exception $e) {
                Log::error("[API ERROR] Error fetching pet ID {$id}: " . $e->getMessage());
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
     * Get the pet by its ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getPetById($id)
    {
        try {
            Log::info("[API REQUEST] Fetching pet ID: {$id}");
            $response = $this->client->get("pet/{$id}");

            $pet = json_decode($response->getBody()->getContents(), true);

            if (empty($pet) || !isset($pet['id'])) {
                Log::warning("[WARNING] Pet ID {$id} not found in API.");
                return null;
            }

            Log::info("[API RESPONSE] Pet found: " . json_encode($pet));
            return $pet;
        } catch (\Exception $e) {
            Log::error("[API ERROR] Error fetching pet ID {$id}: " . $e->getMessage());
            return null;
        }
    }


    /**
     * Add a new pet to the API.
     *
     * @param array $data
     * @return array
     */
    public function addPet($data)
    {
        try {
            Log::info('Adding pet with data:', $data); // Logujemy wysyłane dane

            $response = $this->client->post('pet', [
                'json' => [
                    'id' => rand(1000, 9999),
                    'category' => ['id' => rand(1, 10), 'name' => 'General'],
                    'name' => $data['name'],
                    'photoUrls' => ["https://example.com/default.jpg"],
                    'tags' => [['id' => rand(1, 10), 'name' => 'friendly']],
                    'status' => $data['status'],
                ],
            ]);

            $pet = json_decode($response->getBody()->getContents(), true);

            Log::info('Pet added successfully:', $pet); // Logujemy odpowiedź z API

            return $pet;
        } catch (\Exception $e) {
            Log::error('Error adding pet: ' . $e->getMessage());
            throw $e;
        }
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
        try {
            Log::info("[API REQUEST] Updating pet ID: {$id} with data: " . json_encode($data));

            $payload = [
                'id' => $id,
                'category' => [
                    'id' => $data['category_id'] ?? rand(1, 10),
                    'name' => $data['category_name'] ?? 'General'
                ],
                'name' => $data['name'],
                'photoUrls' => $data['photoUrls'] ?? ["https://example.com/default.jpg"],
                'tags' => $data['tags'] ?? [['id' => rand(1, 10), 'name' => 'friendly']],
                'status' => $data['status'],
            ];

            $this->client->put('pet', ['json' => $payload]);

            Log::info("[API RESPONSE] Pet ID: {$id} updated successfully.");
        } catch (\Exception $e) {
            Log::error("[API ERROR] Error updating pet ID {$id}: " . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Delete a pet from the API.
     *
     * @param int $id
     * @return void
     */
    public function deletePet($id)
    {
        try {
            Log::info("[API REQUEST] Deleting pet ID: {$id}");

            $response = $this->client->delete("pet/{$id}");

            if ($response->getStatusCode() === 200) {
                Log::info("[API RESPONSE] Pet ID {$id} deleted successfully.");
            } else {
                Log::warning("[API WARNING] API responded with status code: " . $response->getStatusCode());
            }
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            if ($e->getResponse()->getStatusCode() == 404) {
                Log::warning("[API ERROR] Pet ID {$id} not found (404). Assuming it's already deleted.");
            } else {
                Log::error("[API ERROR] Error deleting pet ID {$id}: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            Log::error("[ERROR] Unexpected error deleting pet ID {$id}: " . $e->getMessage());
        }
    }
}
