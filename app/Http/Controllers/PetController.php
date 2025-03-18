<?php

namespace App\Http\Controllers;

use App\Http\Requests\PetRequest;
use App\Services\PetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class PetController
 * @package App\Http\Controllers
 */
class PetController extends Controller
{
    protected $petService;

    /**
     * PetController constructor.
     * @param PetService $petService
     */
    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $search = strtolower($request->input('search', ''));
        $filter = $request->input('filter', 'my');

        $petsPagination = ($filter === 'all')
            ? $this->fetchAllPets($limit, $page)
            : $this->fetchMyPets($limit, $page);

        $filteredPets = array_filter($petsPagination['data'], function ($pet) use ($search) {
            return empty($search) || stripos(strtolower($pet['name']), $search) !== false;
        });

        return view('pets.index', [
            'pets' => array_values($filteredPets),
            'total' => count($filteredPets),
            'perPage' => $limit,
            'currentPage' => $page,
            'lastPage' => max(1, ceil($petsPagination['total'] / $limit)),
            'search' => $search,
            'filter' => $filter
        ]);
    }

    /**
     * Fetch all pets from the API.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    private function fetchAllPets($limit, $page)
    {
        Log::info("[API REQUEST] Fetching all pets from API...");
        return $this->petService->getAllPets($limit, $page);
    }

    /**
     * Fetch pets added by the user.
     *
     * @param int $limit
     * @param int $page
     * @return array
     */
    private function fetchMyPets($limit, $page)
    {
        $addedPets = (array) session()->get('added_pets', []);
        Log::info("[SESSION DATA] Retrieved added pets: " . json_encode($addedPets));
        return $this->petService->getPetsByIds($addedPets, $limit, $page);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        return view('pets.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PetRequest $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(PetRequest $request)
    {
        return $this->handlePetOperation(function () use ($request) {
            $pet = $this->petService->addPet($request->validated());

            $addedPets = (array) session()->get('added_pets', []);
            $addedPets[] = $pet['id'];
            session()->put('added_pets', array_values($addedPets));

            Log::info("[SESSION UPDATE] Added pets stored in session: " . json_encode($addedPets));
        }, 'Pet added successfully!', 'Error adding pet.');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($id)
    {
        try {
            $pet = $this->petService->getPetById($id);

            if (!$pet || empty($pet['id'])) {
                Log::warning("[WARNING] Pet with ID {$id} not found in API.");
                return redirect()->route('pets.index')->withErrors("Pet with ID {$id} not found.");
            }

            return view('pets.edit', compact('pet'));
        } catch (\Exception $e) {
            Log::error("[ERROR] Failed to fetch pet with ID {$id}: " . $e->getMessage());
            return redirect()->route('pets.index')->withErrors("Error fetching pet.");
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PetRequest $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(PetRequest $request, $id)
    {
        return $this->handlePetOperation(function () use ($id, $request) {
            $this->petService->updatePet($id, $request->validated());
        }, 'Pet updated successfully!', 'Error updating pet.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        return $this->handlePetOperation(function () use ($id) {
            $this->petService->deletePet($id);

            $addedPets = (array) session()->get('added_pets', []);
            if (($key = array_search($id, $addedPets)) !== false) {
                unset($addedPets[$key]);
                session()->put('added_pets', array_values($addedPets));
                Log::info("[SESSION UPDATE] Removed pet ID {$id} from session. New list: " . json_encode($addedPets));
            }
        }, 'Pet deleted successfully!', 'Error deleting pet.');
    }


    /**
     * Handle pet operations and log success/error messages.
     *
     * @param callable $operation
     * @param string $successMessage
     * @param string $errorMessage
     * @return \Illuminate\Http\RedirectResponse
     */
    private function handlePetOperation(callable $operation, string $successMessage, string $errorMessage)
    {
        try {
            $operation();
            Log::info("[SUCCESS] {$successMessage}");
            session()->flash('success', $successMessage);
            return redirect()->route('pets.index');
        } catch (\Exception $e) {
            Log::error("[ERROR] {$errorMessage}: " . $e->getMessage());
            session()->flash('error', $errorMessage);
            return back();
        }
    }
}
