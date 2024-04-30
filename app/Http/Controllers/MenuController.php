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
        $request->validate([
            'search' => 'string|nullable',
            'menu_id' => 'integer|nullable', 
            'user_id' => 'integer|nullable', 
            'per_page' => 'integer|nullable',
            'page' => 'integer|nullable'
        ]);

        if ($request->has('menu_id')) {
            return $this->show($request->input('menu_id')); 
        }

        $query = Menu::with('user');

        if ($request->has('search')) {
            $searchTerm = $request->input('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                ->orWhere('description', 'like', '%' . $searchTerm . '%')
                ->orWhereHas('user', function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%'); 
                });
            });
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        $perPage = $request->input('per_page', 10); 

        $menus = $query->paginate($perPage);

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
                ksort($mealTimes); 
                $mealTimes = array_values($mealTimes); 
            }
        }
    
        return $weeks;
    }
    

    public function getMealTimesByWeekAndDay(Request $request)
    {
        $validatedData = $request->validate([
            'menu_id' => 'required|integer|exists:menus,menu_id',
            'week' => 'required|integer',
            'day_of_week' => 'required|integer',
        ]);

        $menuId = $validatedData['menu_id'];

        $mealTimes = MenuMealTime::with(['mealDishes'])
                    ->where('menu_id', $menuId)
                    ->where('week', $validatedData['week'])
                    ->where('day_of_week', $validatedData['day_of_week'])
                    ->get();

        if ($mealTimes->isEmpty()) {
            return response()->json(['message' => 'No meal times found for the specified criteria'], 404);
        }


        $totalWeeks = MenuMealTime::select('week')->distinct()->count();
        $daysInWeek = MenuMealTime::where('week', $validatedData['week'])->select('day_of_week')->distinct()->count();

        return response()->json([
            'mealTimes' => $mealTimes,
            'totalWeeks' => $totalWeeks,
            'daysInWeek' => $daysInWeek,
        ]);
    }


    public function updateMenuStatus(Request $request, $id) {
        $menu = Menu::findOrFail($id);
        $newStatus = $request->input('status');
        $comment = $request->input('comment', ''); 
    
        try {
            $oldStatus = $menu->status; 
    
            if ($this->menuStateService->transition($menu, $newStatus)) {
                MenuStatusTransition::create([
                    'menu_id' => $menu->getKey(),
                    // 'user_id' => Auth::id(), // Enable this in production
                    'user_id' => 1, 
                    'from_status' => $oldStatus,
                    'to_status' => $newStatus,
                    'comment' => $comment,
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

        $nutritionTotals = []; 
        $totalDays = 0;

        foreach ($request->input('weeks') as $week) {
            foreach ($week['days'] as $day) {
                $totalDays++;
                $dailyTotals = $this->calculateDailyNutrition($day['meal_times']);

                foreach ($dailyTotals as $key => $value) {
                    if (!isset($nutritionTotals[$key])) {
                        $nutritionTotals[$key] = 0;
                    }
                    $nutritionTotals[$key] += $value;
                }
            }
        }

        $averageDailyNutrition = $this->calculateAverageDailyNutrition($nutritionTotals, $totalDays);

        $nutrientMap = [];
        $nutrientNames = config('nutrients.nutrient_names'); 
        $nutrientMeasurements = config('nutrients.nutrient_mesurement_units');

        foreach ($nutrientNames as $name) {
            if (!isset($nutrientMap[$name])) {
                if ($name === 'protein' || $name === 'fat' || $name === 'carbohydrate') {
                    continue;
                }
                $nutrientMap[$name] = [
                    'name' => $name,
                    'weight' => isset($averageDailyNutrition[$name]) ? $averageDailyNutrition[$name] : 0,
                    'measurement_unit' => $nutrientMeasurements[$name]
                ];
            }
        }

        return response()->json([
            'totals' => [
                'total_price' => 0, 
                'total_weight' => $averageDailyNutrition['weight'] ?? 0,
                'total_kilocalories' => $averageDailyNutrition['kilocalories'] ?? 0,
                'total_protein' => $averageDailyNutrition['protein'] ?? 0,
                'total_fat' => $averageDailyNutrition['fat'] ?? 0,
                'total_carbohydrate' => $averageDailyNutrition['carbohydrate'] ?? 0,
                'nutrient_map' => $nutrientMap
            ]
        ], 200);
    }

    protected function calculateDailyNutrition($mealTimes)
    {
        $totals = [];

        foreach ($mealTimes as $mealTime) {
            foreach ($mealTime['dishes'] as $dishData) {
                $dish = Dish::with('products')->findOrFail($dishData['dish_id']);

                if (isset($dishData['weight'])) {
                    $weight = $dishData['weight'];
                } else {
                    $weight = $dish->weight;
                }

                $coefficient = $weight / $dish->weight;

                $totals['weight'] = $weight;
                $totals['kilocalories'] = (float) $dish->kilocalories * $coefficient;
                $totals['protein'] = $dish->protein * $coefficient;
                $totals['fat'] = $dish->fat * $coefficient;
                $totals['carbohydrate'] = $dish->carbohydrate * $coefficient;

                $nutrientNames = config('nutrients.nutrient_names');

                foreach ($nutrientNames as $name) {
                    $totals[$name] = 0;
                }

                if ($dish->has_relation_with_products) {
                    foreach ($dish->products as $product) {
                        $nutrients = json_decode($product->pivot->nutrients, true); 
                    
                        foreach ($nutrients as $nutrient) {
                            $nutrientName = $nutrient['name']; 
                            $nutrientWeight = $nutrient['pivot']['weight'] * $coefficient; 
                    
                            if (in_array($nutrientName, $nutrientNames)) {
                                if (!isset($totals[$nutrientName])) {
                                    $totals[$nutrientName] = 0; 
                                }
                                $totals[$nutrientName] += $nutrientWeight; 
                            }
                        }
                    }
                }else {
                    foreach ($dish->nutrients as $nutrient) {
                        $nutrientName = $nutrient->name;
                        $nutrientWeight = $nutrient->pivot->weight * $coefficient;
    
                        if (in_array($nutrientName, $nutrientNames)) {
                            if (!isset($totals[$nutrientName])) {
                                $totals[$nutrientName] = 0;
                            }
                            $totals[$nutrientName] += $nutrientWeight;
                        }
                    }
                }
                
            }
        }

        return $totals;
    }

    protected function calculateAverageDailyNutrition($nutritionTotals, $totalDays)
    {
        $averageDailyNutrition = [];

        foreach ($nutritionTotals as $key => $value) {
            $averageDailyNutrition[$key] = $totalDays > 0 ? round($value / $totalDays, 2) : 0;
        }

        return $averageDailyNutrition;
    }


    protected function validateMenuNutritionRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
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
}

