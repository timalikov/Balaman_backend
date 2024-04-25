<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SeedDishesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:seed-dishes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seeds the dishes table by making a request to the API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $url = env('APP_URL') . 'api/dishes';
        $products = config('productid');

        // Function to remove white spaces from array keys
        function removeSpacesFromKeys($array) {
            $newArray = [];
            foreach ($array as $key => $value) {
                $newKey = str_replace(' ', '', $key); // Remove white spaces from key
                $newKey = strtolower($newKey); // Convert to lowercase

                $newArray[$newKey] = $value;
            }
            return $newArray;
        }

        // Preprocess $products keys to remove white spaces
        $processedProducts = removeSpacesFromKeys($products);

        function getProductById($products, $key) {
            $normalizedKey = strtolower(str_replace(' ', '', $key));
            if ($products[$normalizedKey] === null){
                Log::error('Product not found', ['product_name' => $key]);
                throw new Exception("Product not found: {$key}");
            }
            return $products[$normalizedKey];
        }


        $payloads = [
            // ### 1
            [
                "bls_code" => Str::random(10),
                "name" => "Салат из свежих огурцов с растительным маслом",
                // "description" => "",
                "recipe_description" => "Подготовленные огурцы свежие режут тонкими ломтиками и перед отпуском поливают растительным маслом и посыпают луком зеленым",
                "dish_category_id" => 4,
                // "image_url" => "http://example.com/images/salad.jpg",
                // "health_factor" => 5,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Огурец сырой целый'),
                        "weight" => 60,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Лук зеленый сырой'),
                        "factor_ids" => [1],
                        "weight" => 4,
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
                        "weight" => 3,
                        "factor_ids" => [],
                    ]
                ]
            ], 
            // ### 2
            [
                "bls_code" => Str::random(10),
                "name" => "Овощная нарезка",
                "recipe_description" => "Подготовленные огурцы свежие режут тонкими ломтиками и перед отпуском поливают растительным маслом и посыпают луком зеленым",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Морковь сырая целая'),
                        "weight" => 26,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Помидоры сырые целые'),
                        "weight" => 23,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Капуста белокочанная сырая целая'),
                        "weight" => 14,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Сладкий перец сырой целый'),
                        "weight" => 7,
                        "factor_ids" => [1],
                    ]
                ]
            ],
                // ### 3
            [
                "bls_code" => Str::random(10),
                "name" => "Салат из помидоров и огурцов с растительным маслом",
                "recipe_description" => "Подготовленные помидоры и огурцы свежие режут тонкими ломтиками и перед отпуском поливают растительным маслом и посыпают луком зеленым",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Помидоры сырые целые'),
                        "weight" => 44,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Огурец сырой целый'),
                        "weight" => 19,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Лук зеленый сырой'),
                        "weight" => 6,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
                        "weight" => 3,
                        "factor_ids" => [],
                    ]
                ]
            ],
            // ### 4
            [
                "bls_code" => Str::random(10),
                "name" => "Нарезка из помидоров и огурцов",
                "recipe_description" => "Огурцы и помидоры моют, обдают кипятком и режут ломтиками. Раскладывают на тарелку",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Помидоры сырые целые'),
                        "weight" => 45,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Огурец сырой целый'),
                        "weight" => 48,
                        "factor_ids" => [1, 5],
                    ],
                ]
            ],

            // ### 5 ошиибка
            // [
            //     "bls_code" => Str::random(10),
            //     "name" => "Салат с фасолью и огурцами",
            //     "recipe_description" => "Подготовьте все продукты. Фиолетовый лук можно заменить белым салатным или обычным репчатым, но в этом случае салат будет острее. Зелень можете использовать по своим предпочтениям.
            //     С консервированной фасоли слейте жидкость.
            //     Овощи промойте, лук очистите, у огурцов обрежьте края. Лук нарежьте небольшими кусочками.
            //     Огурцы нарежьте крупными кубиками/четвертинками кружочков. Соедините в глубокой миске или салатнике консервированную фасоль, маринованный лук, огурцы и измельчённую зелень петрушки (несколько листиков оставьте для подачи). Перемешайте. Добавьте соль и растительное (оливковое) масло. Снова перемешайте.
            //     ",
            //     "dish_category_id" => 4,
            //     "products" => [
            //         [
            //             "product_id" => getProductById($processedProducts, 'Фасоль огненно-красная консервированная с жидкостью'),
            //             "weight" => 45,
            //             "factor_ids" => [1],
            //         ],
            //         [
            //             "product_id" => getProductById($processedProducts, 'Огурец сырой целый'),
            //             "weight" => 25,
            //             "factor_ids" => [1],
            //         ],
            //         [
            //             "product_id" => getProductById($processedProducts, 'Лук репчатый сырой целый'),
            //             "weight" => 14,
            //             "factor_ids" => [1],
            //         ],
            //         [
            //             "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
            //             "weight" => 5,
            //             "factor_ids" => [],
            //         ],
            //         [
            //             "product_id" => getProductById($processedProducts, 'Йодированная соль'),
            //             "weight" => 0.2,
            //             "factor_ids" => [],
            //         ],
            //     ]
            // ],

            ### 6
            [
                "bls_code" => Str::random(10),
                "name" => "Салат из свеклы с зеленым горошком",
                "recipe_description" => "Свеклу отваривают в кожуре 1 час, погружают в холодную воду и выдерживают в ней 30 мин, после чего очищают, шинкуют и смешивают с зеленым горошком. При подаче заправляют растительным маслом.",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Свекла сырая целая'),
                        "weight" => 50,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Горошек зеленый.Консервы'),
                        "weight" => 20,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
                        "weight" => 3,
                        "factor_ids" => [],
                    ]
                ]
            ],

            ### 7
            [
                "bls_code" => Str::random(10),
                "name" => "Салат из свеклы с сыром",
                "recipe_description" => "Свеклу отваривают в кожуре 1 час, погружают в холодную воду и выдерживают в ней 30 мин, после чего очищают, шинкуют и смешивают с нарезанным сыром. При подаче заправляют растительным маслом.",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Свекла сырая целая'),
                        "weight" => 50,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Полутвердый сыр  не менее 40% жирности'),
                        "weight" => 15,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
                        "weight" => 3,
                        "factor_ids" => [],
                    ]
                ]
            ],

            ### 8
            [
                "bls_code" => Str::random(10),
                "name" => "Салат с овощами и яблоками",
                "recipe_description" => "Вареные картофель и свеклу нарезают кубиками, смешивают с яблоками (предварительно их очистив, удалив семенное гнездо) и солеными огурцами, нарезанными ломтиками, заправляют растительным маслом. Салат оформляют ломтиками яблок и зеленью петрушки.",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Картофель  сырой целый'),
                        "weight" => 35,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Яблоко сырое целое'),
                        "weight" => 25,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Свекла сырая целая'),
                        "weight" => 32,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Огурец консервированный без жидкости '),
                        "weight" => 15,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Подсолнечное масло'),
                        "weight" => 5,
                        "factor_ids" => [],
                    ]
                ]
            ],

            ### 9
            [
                "bls_code" => Str::random(10),
                "name" => "Бутерброд с сыром, масло сливочным и свеклой вареной",
                "recipe_description" => "Выдается порционно",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Свекла сырая целая '),
                        "weight" => 50,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Полутвердый сыр  не менее 40% жирности  '),
                        "weight" => 30,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Масло сливочное'),
                        "weight" => 5,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Ржано-пшеничный хлеб'),
                        "weight" => 20,
                        "factor_ids" => [],
                    ]
                ]
            ],

            ### 10
            [
                "bls_code" => Str::random(10),
                "name" => "Бутерброд с сыром и огурцом",
                "recipe_description" => "Выдается порционно",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Полутвердый сыр  не менее 40% жирности  '),
                        "weight" => 10,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Огурец сырой целый'),
                        "weight" => 25,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Масло сливочное'),
                        "weight" => 5,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Ржано-пшеничный хлеб'),
                        "weight" => 20,
                        "factor_ids" => [],
                    ]
                ]
            ],

            ### 11
            [
                "bls_code" => Str::random(10),
                "name" => "Ежики из говядины",
                "recipe_description" => "В готовый фарш добавляем рис. Рис отвариваем заранее. Предпочтительнее - круглозерный белый. Куриное яйцо и соль. Перемешиваем ингредиенты в однородный фарш. Пассеруем репчатый лук. Его предварительно нужно порезать мелкими кубиками. Добавляем лук в фарш. Тщательно перемешиваем. Мокрыми руками формируем из фарша шарики из расчета: на 1 порцию - 3-4 ежика. Обваливаем в муке. Складываем шарики в форму для запекания. Форму предварительно смазываем сливочным маслом. Отправляем форму с будущими тефтелями в духовку (при 220°C). Ежики должны подрумяниться. Пока они поджариваются, приготовим молочный белый соус для запекания. В отдельной сковороде подсушиваем до легкого карамельного оттенка муку. Растираем со сливочным маслом. Массу перекладываем в кастрюльку и заливаем горячим молоком. Соус необходимо всё время перемешивать, а когда загустеет - процедить. Заливаем ежики разведенным с водой соусом. Отправляем назад в духовку. Готовим еще 15 минут при максимальном нагреве (около 250°C). Ежики как в детском саду готовы! Их принято подавать с гарниром, поливая тефтели соусом.",
                "dish_category_id" => 4,
                "products" => [
                    [
                        "product_id" => getProductById($processedProducts, 'Филе говядины сырое'),
                        "weight" => 81,
                        "factor_ids" => [1, 5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Рис круглозерновой '),
                        "weight" => 15,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Лук репчатый сырой целый'),
                        "weight" => 25,
                        "factor_ids" => [1],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Яйцо целое куриное'),
                        "weight" => 18,
                        "factor_ids" => [1,5],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Мука пшеничная сорт 550 '),
                        "weight" => 8,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Молоко пастеризованное,1,5°/о жирности'),
                        "weight" => 40,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Масло сливочное'),
                        "weight" => 5,
                        "factor_ids" => [],
                    ],
                    [
                        "product_id" => getProductById($processedProducts, 'Йодированная соль'),
                        "weight" => 0.3,
                        "factor_ids" => [],
                    ]
                ]
            ],

            
            
                
        ];

        foreach ($payloads as $payload) {
            $response = Http::post($url, $payload);

            if ($response->successful()) {
                $this->info('Dishes table has been successfully seeded.');
            } else {
                $this->error('Failed to seed dishes table. Response status: ' . $response->status());
            }
        }
    }
}
