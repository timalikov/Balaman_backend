<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuMealTime;
use App\Models\MealDish;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Dish;
use App\Services\MenuStateService;
use App\Models\MenuStatusTransition;




class MenuController extends Controller
{
    private $menuStateService;

    public function __construct(MenuStateService $menuStateService) {
        $this->menuStateService = $menuStateService;
    }


    // public function __construct()
    // {
    //     // Assuming JWT authentication is done in the auth:api middleware
    //     $this->middleware('auth:api');
    // }

    /**
     * Display a listing of all menus.
     */
    public function index(Request $request)
    {
        // Validate the request
        $request->validate([
            'search' => 'string|nullable',
            'menu_id' => 'integer|nullable', // Assuming you want to search by menu_id
            'user_id' => 'integer|nullable', // Assuming you want to filter by user_id
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable'
        ]);

        // Check for specific menu ID search
        if ($request->has('menu_id')) {
            return $this->show($request->input('menu_id')); // Ensure you have a show method to handle this
        }

        // Start the query
        $query = Menu::with('user');

        // Handle the general search parameter
        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%'); // Assuming 'name' is a searchable field in the User model
                });
            });
        }

        // Filter by user_id if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Determine the number of menus per page
        $perPage = $request->input('per_page', 10); // Default to 10 if not provided

        // Get the results with pagination
        $menus = $query->paginate($perPage);

        // Optional: Customize the response format if needed
        return response()->json([
            'current_page' => $menus->currentPage(),
            'items_per_page' => $menus->perPage(),
            'total_items' => $menus->total(),
            'total_pages' => $menus->lastPage(),
            'data' => $menus->items()
        ]);
    }
    

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

         // Set status to 'draft' automatically when menu is created
        $validatedData['status'] = 'draft';

        // Extract user ID from JWT token
        // $userId = Auth::id();
        $userId = 1;
        $validatedData['user_id'] = $userId;

        $menu = Menu::create($validatedData);

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function show($id)
    {
        $menu = Menu::with(['menuMealTimes.mealTime', 'menuMealTimes.mealDishes'])
                    ->findOrFail($id);

        $weeks = [];

        foreach ($menu->menuMealTimes as $menuMealTime) {
            $week = $menuMealTime->week;
            $dayOfWeek = $menuMealTime->day_of_week;
            $mealTimeId = $menuMealTime->meal_time_id;

            if (!isset($weeks[$week])) {
                $weeks[$week] = [];
            }

            if (!isset($weeks[$week][$dayOfWeek])) {
                $weeks[$week][$dayOfWeek] = [];
            }

            // Check if the mealTimeId already exists for that day
            if (!isset($weeks[$week][$dayOfWeek][$mealTimeId])) {
                // Initialize the mealTime with its dishes if it doesn't exist
                $weeks[$week][$dayOfWeek][$mealTimeId] = [
                    'mealTime' => $menuMealTime->mealTime->toArray(),
                    'dishes' => []
                ];
            }

            // Append dishes to the existing mealTime
            $weeks[$week][$dayOfWeek][$mealTimeId]['dishes'] = array_merge(
                $weeks[$week][$dayOfWeek][$mealTimeId]['dishes'],
                $menuMealTime->mealDishes->toArray()
            );
        }

        // Sort weeks and days within weeks, and reset the mealTime keys to ensure a list format
        ksort($weeks);
        foreach ($weeks as $week => &$days) {
            ksort($days);
            foreach ($days as $day => &$mealTimes) {
                $mealTimes = array_values($mealTimes); // Reset keys to ensure list format for JSON
            }
        }

        $response = [
            'menu_id' => $menu->menu_id,
            'name' => $menu->name,
            'description' => $menu->description,
            'user_id' => $menu->user_id,
            'status' => $menu->status,
            'season' => $menu->season,
            'created_at' => $menu->created_at,
            'updated_at' => $menu->updated_at,
            'weeks' => $weeks,
        ];

        return response()->json($response);
    }




    public function createMealPlan(Request $request) {
        $validator = Validator::make($request->all(), [
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'week' => 'required|integer|min:1',
            'day_of_week' => 'required|integer|between:1,7',
            'meal_times' => 'required|array',
            'meal_times.*.meal_time_id' => 'required|integer|exists:meal_times,meal_time_id',
            'meal_times.*.dishes' => 'required|array',
            'meal_times.*.dishes.*.dish_id' => 'required|integer|exists:dishes,dish_id',
            'meal_times.*.dishes.*.weight' => 'sometimes|numeric',
        ]);
        
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $validatedData = $validator->validated();
                
        $menuId = $validatedData['menu_id'];
    
        // $menu = Menu::where('menu_id', $menuId)->where('user_id', Auth::id())->first();
        $menu = Menu::where('menu_id', $menuId)->first();

        if (!$menu) {
            return response()->json(['message' => 'Menu not found or access denied'], 404);
        }
    
        DB::beginTransaction();
        try {
            foreach ($validatedData['meal_times'] as $mealTimeData) {
                $menuMealTime = MenuMealTime::firstOrCreate([
                    'menu_id' => $menuId,
                    'week' => $validatedData['week'],
                    'day_of_week' => $validatedData['day_of_week'],
                    'meal_time_id' => $mealTimeData['meal_time_id'],
                ]);
    
                foreach ($mealTimeData['dishes'] as $dishData) {

                    // Check if weight is provided in the request for this dish
                    if (isset($dishData['weight'])) {
                        $weight = $dishData['weight'];
                    } else {
                        // Fetch dish weight from the database if not provided in the request
                        $dish = Dish::find($dishData['dish_id']);
                        $weight = $dish ? $dish->weight : null; 
                    }

                    $menuMealTime->mealDishes()->updateOrCreate([
                        'dish_id' => $dishData['dish_id'],
                    ], [
                        'weight' => $weight,
                    ]);
                }
            }
    
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create daily meal plan', 'error' => $e->getMessage()], 500);
        }
    
        // Reload the menu to include the newly added meal times and dishes
        $menu->load(['menuMealTimes' => function ($query) use ($validatedData) {
            $query->where('week', $validatedData['week'])
                  ->where('day_of_week', $validatedData['day_of_week']);
        }, 'menuMealTimes.mealTime', 'menuMealTimes.mealDishes']);
    
        return response()->json(['message' => 'Daily meal plan created successfully', 'menu' => $menu], 201);
    }
    
    
    
    

    public function getMealTimesByWeekAndDay(Request $request)
    {
        // Validate the incoming request parameters
        $validatedData = $request->validate([
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'week' => 'required|integer',
            'day_of_week' => 'required|integer',
        ]);

        $menuId = $validatedData['menu_id'];

        // Retrieve meal times and dishes for the specified menu, week, and day of the week
        $mealTimes = MenuMealTime::with(['mealDishes'])
                    ->where('menu_id', $menuId)
                    ->where('week', $validatedData['week'])
                    ->where('day_of_week', $validatedData['day_of_week'])
                    ->get();

        if ($mealTimes->isEmpty()) {
            return response()->json(['message' => 'No meal times found for the specified criteria'], 404);
        }


        // Calculate the total number of weeks and days available
        $totalWeeks = MenuMealTime::select('week')->distinct()->count();
        $daysInWeek = MenuMealTime::where('week', $validatedData['week'])->select('day_of_week')->distinct()->count();

        return response()->json([
            'mealTimes' => $mealTimes,
            'totalWeeks' => $totalWeeks,
            'daysInWeek' => $daysInWeek,
        ]);
    }

    public function addDishToMenu(Request $request)
    {
        $validated = $request->validate([
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'dish_id' => 'required|integer|exists:dishes,dish_id',
            'weight' => 'sometimes|numeric',
        ]);

        $menu = Menu::findOrFail($validated['menu_id']);
        $weight = $validated['weight'] ?? Dish::find($validated['dish_id'])->weight;

        if ($menu->menuMealTimes->isEmpty()) {
            $menuMealTime = $this->createDefaultMenuMealTime($validated['menu_id']);
            // Attach the dish to the newly created default menu meal time
            $menuMealTime->mealDishes()->attach($validated['dish_id'], ['weight' => $weight]);
        } else {
            $additionalValidation = $request->validate([
                'meal_time_id' => 'required|integer|exists:meal_times,meal_time_id',
                'week' => 'required|integer|min:1',
                'day_of_week' => 'required|integer|between:1,7',
            ]);

            $menuMealTime = MenuMealTime::firstOrCreate(
                array_merge(['menu_id' => $validated['menu_id']], $additionalValidation),
                ['menu_id' => $validated['menu_id']]
            );

            // Check if the dish already exists in the meal time
            $existingDish = $menuMealTime->mealDishes()->where('dish_id', $validated['dish_id'])->first();

            if ($existingDish) {
                // Update the weight if the dish already exists
                $menuMealTime->mealDishes()->updateExistingPivot($validated['dish_id'], ['weight' => $weight]);
            } else {
                // Attach the dish if it's not already associated
                $menuMealTime->mealDishes()->attach($validated['dish_id'], ['weight' => $weight]);
            }
        }

        return response()->json([
            'message' => 'Dish added successfully to the specified day and meal time',
            'mealTime' => $menuMealTime->load('mealDishes'),
        ]);
    }

    protected function createDefaultMenuMealTime($menuId)
    {
        return MenuMealTime::create([
            'menu_id' => $menuId,
            'week' => 1,
            'day_of_week' => 1,
            'meal_time_id' => 1,
        ]);
    }


    public function removeDishFromMenu(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'meal_time_id' => 'required|integer|exists:meal_times,meal_time_id',
            'week' => 'required|integer|min:1',
            'day_of_week' => 'required|integer|between:1,7',
            'dish_id' => 'required|integer|exists:dishes,dish_id',
        ]);

        // Attempt to find the specific MenuMealTime entry
        $menuMealTime = MenuMealTime::where([
            'menu_id' => $validated['menu_id'],
            'meal_time_id' => $validated['meal_time_id'],
            'week' => $validated['week'],
            'day_of_week' => $validated['day_of_week'],
        ])->first();

        if (!$menuMealTime) {
            // If the meal time does not exist, return an error
            return response()->json(['message' => 'Specified meal time not found for the given day and week'], 404);
        }

        // Attempt to detach the dish from the meal time
        $detached = $menuMealTime->mealDishes()->detach($validated['dish_id']);

        if ($detached) {
            return response()->json(['message' => 'Dish removed successfully from the specified day and meal time']);
        } else {
            // If the dish was not found or could not be removed, return an error
            return response()->json(['message' => 'Failed to remove the dish or dish was not found in the specified meal time'], 404);
        }
    }


    public function updateMenuStatus(Request $request, $id) {
        $menu = Menu::findOrFail($id);
        $newStatus = $request->input('status');
        $comment = $request->input('comment', ''); // Default to empty string if not provided
    
        try {
            $oldStatus = $menu->status; // Store old status for logging
    
            // Perform the status transition
            if ($this->menuStateService->transition($menu, $newStatus)) {
                // Log the status transition
                MenuStatusTransition::create([
                    'menu_id' => $menu->getKey(),
                    // 'user_id' => Auth::id(), // Assuming you want to log the ID of the authenticated user
                    'user_id' => 1, 
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'comment' => $comment, // Save the provided comment
                ]);
    
                return response()->json(['message' => 'Menu status updated successfully.']);
            }
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }


    

}

