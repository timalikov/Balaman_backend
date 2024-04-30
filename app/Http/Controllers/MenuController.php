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
use Exception;
use Illuminate\Support\Facades\Log;





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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weeks' => 'required|array|min:1', 
        
            'weeks.*.week_number' => 'required|integer|between:1,52',  
            'weeks.*.days' => 'required|array|min:1',  
        
            'weeks.*.days.*.day_number' => 'required|integer|between:1,7',  
            'weeks.*.days.*.meal_times' => 'required|array|min:1',  
        
            'weeks.*.days.*.meal_times.*.meal_time_number' => 'required|integer',
            'weeks.*.days.*.meal_times.*.meal_time_name' => 'required|string',  
            'weeks.*.days.*.meal_times.*.dishes' => 'required|array|min:1', 
        
            'weeks.*.days.*.meal_times.*.dishes.*.dish_id' => 'required|integer|exists:dishes,dish_id',  
            'weeks.*.days.*.meal_times.*.dishes.*.weight' => 'sometimes|numeric',  
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);  
        }
        
        $validatedData = $validator->validated();

        $validatedData['status'] = 'draft';

        // Extract user ID from JWT token
        $userId = 1; // Replace with `Auth::id()` in production
        $validatedData['user_id'] = $userId;

        DB::beginTransaction();
        try {
            $menu = Menu::create([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
                'user_id' => $userId,
                'status' => $validatedData['status'],
            ]);

            if (isset($validatedData['weeks'])) {
                foreach ($validatedData['weeks'] as $weekIndex => $week) {
                    foreach ($week['days'] as $dayIndex => $day) {
                        foreach ($day['meal_times'] as $mealTime) {
                            Log::info('About to insert meal time', ['meal_time' => $mealTime['meal_time_name']]);

                            $menuMealTime = $menu->menuMealTimes()->create([
                                'week' => $week['week_number'],
                                'day_of_week' => $day['day_number'],
                                'meal_time_name' => $mealTime['meal_time_name'],
                                'meal_time_number' => $mealTime['meal_time_number'],
                            ]);
                            Log::info('Meal time inserted', ['menuMealTime' => $menuMealTime]);

                            foreach ($mealTime['dishes'] as $dishData) {
                                $weight = isset($dishData['weight']) ? $dishData['weight'] : Dish::find($dishData['dish_id'])->weight;

                                $menuMealTime->mealDishes()->attach($dishData['dish_id'], ['weight' => $weight]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Menu and meal plan created successfully.', 'menu' => $menu], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to create menu and meal plan', 'error' => $e->getMessage()], 500);
        }

    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'weeks' => 'required|array|min:1',

            'weeks.*.week_number' => 'required|integer|between:1,52',
            'weeks.*.days' => 'required|array|min:1',

            'weeks.*.days.*.day_number' => 'required|integer|between:1,7',
            'weeks.*.days.*.meal_times' => 'required|array|min:1',

            'weeks.*.days.*.meal_times.*.meal_time_id' => 'required|integer|exists:meal_times,meal_time_id',
            'weeks.*.days.*.meal_times.*.dishes' => 'required|array|min:1',

            'weeks.*.days.*.meal_times.*.dishes.*.dish_id' => 'required|integer|exists:dishes,dish_id',
            'weeks.*.days.*.meal_times.*.dishes.*.weight' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validatedData = $validator->validated();

        $menu = Menu::findOrFail($id);  

        DB::beginTransaction();
        try {
            $menu->update([
                'name' => $validatedData['name'],
                'description' => $validatedData['description'],
            ]);

            $menu->menuMealTimes()->delete();  

            // Repopulate the menu details
            if (isset($validatedData['weeks'])) {
                foreach ($validatedData['weeks'] as $week) {
                    foreach ($week['days'] as $day) {
                        foreach ($day['meal_times'] as $mealTime) {
                            $menuMealTime = $menu->menuMealTimes()->create([
                                'week' => $week['week_number'],
                                'day_of_week' => $day['day_number'],
                                'meal_time_id' => $mealTime['meal_time_id'],
                            ]);

                            foreach ($mealTime['dishes'] as $dishData) {
                                $weight = isset($dishData['weight']) ? $dishData['weight'] : Dish::find($dishData['dish_id'])->weight;
                                $menuMealTime->mealDishes()->attach($dishData['dish_id'], ['weight' => $weight]);
                            }
                        }
                    }
                }
            }

            DB::commit();
            return response()->json(['message' => 'Menu and meal plan updated successfully.', 'menu' => $menu], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update menu and meal plan', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        $menu = Menu::with(['menuMealTimes.mealDishes'])
                    ->findOrFail($id);
    
        $weeks = $this->organizeMealPlan($menu);
    
        $response = [
            'menu_id' => $menu->menu_id,
            'name' => $menu->name,
            'description' => $menu->description,
            'user_id' => $menu->user_id,
            'status' => $menu->status,
            'weeks' => $weeks,
            'created_at' => $menu->created_at->toDateTimeString(),
            'updated_at' => $menu->updated_at->toDateTimeString(),
        ];
    
        return response()->json($response);
    }
    
    protected function organizeMealPlan($menu)
    {
        $weeks = [];
    
        foreach ($menu->menuMealTimes as $menuMealTime) {
            $weekNumber = $menuMealTime->week;
            $dayNumber = $menuMealTime->day_of_week;
            $mealTimeName = $menuMealTime->meal_time_name;
            $mealTimeNumber = $menuMealTime->meal_time_number;
    
            if (!isset($weeks[$weekNumber])) {
                $weeks[$weekNumber] = [];
            }
            if (!isset($weeks[$weekNumber][$dayNumber])) {
                $weeks[$weekNumber][$dayNumber] = [];
            }
    
            if (!isset($weeks[$weekNumber][$dayNumber][$mealTimeNumber])) {
                $weeks[$weekNumber][$dayNumber][$mealTimeNumber] = [
                    'mealTimeName' => $mealTimeName,
                    'mealTimeNumber' => $mealTimeNumber,
                    'dishes' => []
                ];
            }
    
            foreach ($menuMealTime->mealDishes as $dish) {
                $weeks[$weekNumber][$dayNumber][$mealTimeNumber]['dishes'][] = $dish->toArray();
            }
        }
    
        return $this->sortWeeksAndDays($weeks);
    }
    
    protected function sortWeeksAndDays($weeks)
    {
        ksort($weeks);
        foreach ($weeks as $week => &$days) {
            ksort($days);
            foreach ($days as $day => &$mealTimes) {
                ksort($mealTimes); // Sorting by meal_time_number to maintain order
                $mealTimes = array_values($mealTimes); // Ensure list format
            }
        }
    
        return $weeks;
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
                'meal_time_name' => 'required|string',
                'meal_time_number' => 'required|integer',
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

    public function calculateMenuNutrition(Request $request)
    {

        $this->validateMenuNutritionRequest($request);

        // $menu = Menu::findOrFail($request->input('menu_id'));

        // Initialize total nutrition values
        $nutritionTotals = ['kcal' => 0, 'protein' => 0, 'carbs' => 0, 'fat' => 0];
        $totalDays = 0;
        

        foreach ($request->input('weeks') as $week) {
            foreach ($week['days'] as $day) {
                $totalDays++;
                $dailyTotals = $this->calculateDailyNutrition($day['meal_times']);

                // Aggregate daily totals into overall nutrition totals
                foreach ($nutritionTotals as $key => &$value) {
                    $value += $dailyTotals[$key];
                }
            }
        }

        // Calculate average values for 1 day
        $averageDailyNutrition = $this->calculateAverageDailyNutrition($nutritionTotals, $totalDays);

        // Return the calculated averages in the response
        return response()->json([
            'average_daily_nutrition' => $averageDailyNutrition,
            'total_days' => $totalDays
        ], 200);
    }

    protected function validateMenuNutritionRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // 'menu_id' => 'required|integer|exists:menus,menu_id',
            'weeks' => 'required|array',
            'weeks.*.days' => 'required|array',
            'weeks.*.days.*.meal_times' => 'required|array',
            'weeks.*.days.*.meal_times.*.meal_time_id' => 'required|integer|exists:meal_times,meal_time_id',
            'weeks.*.days.*.meal_times.*.dishes' => 'required|array',
            'weeks.*.days.*.meal_times.*.dishes.*.dish_id' => 'required|integer|exists:dishes,dish_id',
            'weeks.*.days.*.meal_times.*.dishes.*.weight' => 'sometimes|numeric',
        ]);

        if ($validator->fails()) {
            abort(response()->json($validator->errors(), 422));
        }

    }

    protected function calculateDailyNutrition($mealTimes)
    {
        $totals = [
            'kcal' => 0, 
            'protein' => 0, 
            'fat' => 0,
            'carbs' => 0, 
        ];

        foreach ($mealTimes as $mealTime) {
            foreach ($mealTime['dishes'] as $dishData) {
                $dish = Dish::findOrFail($dishData['dish_id']);
                $weight = isset($dishData['weight']) ? $dishData['weight'] : $dish->weight;

                if (isset($dishData['weight']) && $dishData['weight'] != 0) {
                    $totals['kcal'] += $dish->kcal * $weight / 100;
                    $totals['protein'] += $dish->protein * $weight / 100;
                    $totals['carbs'] += $dish->carbs * $weight / 100;
                    $totals['fat'] += $dish->fat * $weight / 100;
                } else {
                    $totals['kcal'] += $dish->kcal;
                    $totals['protein'] += $dish->protein;
                    $totals['carbs'] += $dish->carbs;
                    $totals['fat'] += $dish->fat;
                }
            }
        }

        return $totals;
    }

    protected function calculateAverageDailyNutrition($nutritionTotals, $totalDays)
    {
        $averageDailyNutrition = [];

        foreach ($nutritionTotals as $key => $value) {
            $averageDailyNutrition[$key] = $totalDays > 0 ? $value / $totalDays : 0;
        }

        return $averageDailyNutrition;
    }
}

