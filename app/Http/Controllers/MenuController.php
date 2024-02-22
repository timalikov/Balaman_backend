<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Menu;
use Illuminate\Support\Facades\Auth;
use App\Models\MenuMealTime;
use App\Models\MealDish;


class MenuController extends Controller
{

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
            'season' => 'required|in:spring,summer,autumn,winter',
        ]);

         // Set status to 'pending' automatically when menu is created
        $validatedData['status'] = 'pending';

        // Extract user ID from JWT token
        // $userId = Auth::id();
        $userId = 1;
        $validatedData['user_id'] = $userId;

        $menu = Menu::create($validatedData);

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
    }

    public function show($id)
    {
        // Retrieve the menu details without loading the meal times relationship
        $menu = Menu::with(['menuMealTimes.mealTime', 'menuMealTimes.mealDishes'])
        ->findOrFail($id);

        if (!$menu) {
            return response()->json(['message' => 'Menu not found'], 404);
        }

        // Calculate the number of unique weeks without loading the meal times array
        $numberOfWeeks = MenuMealTime::where('menu_id', $id)
                            ->select('week')
                            ->distinct()
                            ->count();

        // Prepare the response data, excluding the menu_meal_times array
        $response = $menu->toArray();
        $response['number_of_weeks'] = $numberOfWeeks;

        return response()->json($response);
    }


    public function createMealPlan(Request $request, $menuId) {
        $validatedData = $request->validate([
            'meal_times' => 'required|array',
            'meal_times.*.day_of_week' => 'required|integer|between:1,7',
            'meal_times.*.week' => 'required|integer|min:1',
            'meal_times.*.meal_time_id' => 'required|integer',
            'meal_times.*.dishes' => 'sometimes|array',
            'meal_times.*.dishes.*.dish_id' => 'required_with:meal_times.*.dishes|integer',
            'meal_times.*.dishes.*.weight' => 'required_with:meal_times.*.dishes|numeric',
        ]);
        
    
        // Check if the menu exists and belongs to the authenticated user
        $menu = Menu::where('menu_id', $menuId)->where('user_id', Auth::id())->first();
    
        if (!$menu) {
            return response()->json(['message' => 'Menu not found or access denied'], 404);
        }

        $menuId = $request->menuId;

        foreach ($validatedData['meal_times'] as $mealTimeData) {
            // Assuming MenuMealTime has a relationship setup with Menu
            $menuMealTime = MenuMealTime::create([
                'menu_id' => $menuId,
                'day_of_week' => $mealTimeData['day_of_week'],
                'week' => $mealTimeData['week'],
                'meal_time_id' => $mealTimeData['meal_time_id'],
                // Add other necessary fields
            ]);

            if (!empty($mealTimeData['dishes'])) {
                foreach ($mealTimeData['dishes'] as $dish) {
                    $menuMealTime->mealDishes()->create([
                        'dish_id' => $dish['dish_id'],
                        'weight' => $dish['weight'],
                    ]);
                }
            }
        }
        $menuMealTime->load('mealDishes');
    
        return response()->json($menuMealTime, 201);
    }

    public function getMealTimesByWeekAndDay(Request $request, $menuId)
    {
        // Validate the incoming request parameters
        $validatedData = $request->validate([
            'week' => 'required|integer',
            'day_of_week' => 'required|integer',
        ]);
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

    public function addDishToMealTime(Request $request, $menuId)
    {
        // Validate the incoming request parameters
        $validatedData = $request->validate([
            'mealTimeId' => 'required|integer',
            'day_of_week' => 'required|integer',
        ]);

        // Check if the menu exists and belongs to the authenticated user
        $menu = Menu::where('menu_id', $menuId)->where('user_id', Auth::id())->first();

    }


    

}

