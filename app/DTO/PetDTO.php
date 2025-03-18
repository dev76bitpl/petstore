<?php

namespace App\DTO;

/**
 * Class PetDTO
 * @package App\DTO
 */
class PetDTO
{
    public int $id;
    public string $name;
    public string $status;
    public ?int $category_id;
    public ?string $category_name;
    public array $photoUrls;
    public array $tags;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
        $this->status = $data['status'];
        $this->category_id = $data['category']['id'] ?? null;
        $this->category_name = $data['category']['name'] ?? null;
        $this->photoUrls = $data['photoUrls'] ?? [];
        $this->tags = $data['tags'] ?? [];
    }

    /**
     * Pomocnicza metoda do tworzenia wielu DTO naraz
     *
     * @param array $pets
     * @return PetDTO[]
     */
    public static function collection(array $pets): array
    {
        return array_map(fn($pet) => new self($pet), $pets);
    }
}
