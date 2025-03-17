<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PetService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;

class PetController extends Controller
{
    protected $petService;

    public function __construct(PetService $petService)
    {
        $this->petService = $petService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $limit = $request->input('limit', 10);
        $page = $request->input('page', 1);
        $search = strtolower($request->input('search', ''));
        $filter = $request->input('filter', 'my');

        if ($filter === 'all') {
            Log::info("[API REQUEST] Fetching all pets from API...");
            $petsPagination = $this->petService->getAllPets($limit, $page);
        } else {
            // Pobieramy ID zwierząt dodanych przez użytkownika
            $addedPets = session()->get('added_pets', []);

            // **Wymuszenie konwersji na tablicę**
            if (is_string($addedPets)) {
                Log::warning("[WARNING] Session 'added_pets' is a string, converting...");
                $addedPets = json_decode($addedPets, true);
            }

            if (!is_array($addedPets)) {
                Log::error("[ERROR] Failed to convert session 'added_pets' to an array. Resetting...");
                $addedPets = [];
            }

            Log::info("[SESSION DATA] Retrieved added pets: " . json_encode($addedPets));
            $petsPagination = $this->petService->getPetsByIds($addedPets, $limit, $page);
        }

        // Filtrujemy po nazwie
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('pets.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'status' => 'required|string|in:available,pending,sold',
        ]);

        try {
            $pet = $this->petService->addPet($validated);

            Log::info("[SUCCESS] Pet stored successfully with ID: " . json_encode($pet['id'] ?? 'Unknown'));

            // **Zapisujemy dodane zwierzę w sesji jako tablicę**
            $addedPets = session()->get('added_pets', []);

            // **Upewniamy się, że to tablica**
            if (!is_array($addedPets)) {
                Log::warning("[WARNING] Session 'added_pets' was not an array, resetting...");
                $addedPets = [];
            }

            // **Dodajemy nowe ID do tablicy**
            $addedPets[] = $pet['id'];
            session()->put('added_pets', $addedPets);

            Log::info("[SESSION UPDATE] Added pets stored in session: " . json_encode($addedPets));

            // **Dodanie komunikatu o sukcesie**
            session()->flash('success', 'Pet added successfully!');

            return redirect()->route('pets.index');
        } catch (\Exception $e) {
            Log::error("[ERROR] Failed to add pet: " . $e->getMessage());

            // **Dodanie komunikatu o błędzie**
            session()->flash('error', 'Error adding pet.');

            return back();
        }
    }

    /**
     * Edit the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
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
     * Update the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Log::info("[REQUEST] Update request for pet ID: {$id} with data: ", $request->all());

        $validated = $request->validate([
            'name' => 'required|string',
            'status' => 'required|string|in:available,pending,sold',
            'category_id' => 'nullable|integer',
            'category_name' => 'nullable|string',
            'photoUrls' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        try {
            $this->petService->updatePet($id, $validated);
            Log::info("[SUCCESS] Pet ID: {$id} updated successfully.");

            // **Dodanie komunikatu o sukcesie**
            session()->flash('success', 'Pet updated successfully!');

            return redirect()->route('pets.index');
        } catch (\Exception $e) {
            Log::error("[ERROR] Failed to update pet ID {$id}: " . $e->getMessage());

            // **Dodanie komunikatu o błędzie**
            session()->flash('error', 'Error updating pet.');

            return back();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            Log::info("[REQUEST] Attempting to delete pet ID: {$id}");

            $this->petService->deletePet($id);

            // Usuwamy ID z sesji użytkownika
            $addedPets = session()->get('added_pets', []);

            if (is_string($addedPets)) {
                Log::warning("[WARNING] Session 'added_pets' was a string, converting...");
                $addedPets = json_decode($addedPets, true);
            }

            if (!is_array($addedPets)) {
                $addedPets = [];
            }

            if (($key = array_search($id, $addedPets)) !== false) {
                unset($addedPets[$key]);
                session()->put('added_pets', array_values($addedPets));
                Log::info("[SESSION UPDATE] Removed pet ID {$id} from session. New list: " . json_encode($addedPets));
            }

            // **Dodanie komunikatu o sukcesie**
            session()->flash('success', 'Pet deleted successfully!');

            return redirect()->route('pets.index');
        } catch (\Exception $e) {
            Log::error("[ERROR] Failed to delete pet ID {$id}: " . $e->getMessage());

            // **Dodanie komunikatu o błędzie**
            session()->flash('error', 'Error deleting pet.');

            return back();
        }
    }
}
