product_id_map = {'Абрикос сушеный (курага)': 1,
'Апельсин сырой целый': 2,
'Баклажаны сырые целые': 3,
'Банан сырой целый': 4,
'Белый пшеничный хлеб': 5,
'Белый хлеб без глютена': 6,
'Фасоль огненно-красная консервированная с жидкостью': 7,
'Фасоль огненно-красная сырая': 8,
'Брокколи сырая целая': 9,
'Говядина сырая': 10,
'Горох зеленый сушеный ': 11,
'Горошек зеленый консервированный с жидкостью': 12,
'Гречневая крупа': 13,
'Грудка индейки сырая': 14,
'Груша сырая целая': 15,
'Дрожжи': 16,
'Изюм': 17,
'Йогурт   (общее понятие ) 1,5% жирности': 18,
'Йогуртовый напиток 3,5% жирности несладкий': 19,
'Йодированная соль': 20,
'Кабачки сырые': 21,
'Капуста белокочанная сырая целая': 22,
'Картофель  сырой целый': 23,
'Квашеная капуста (6)': 24,
'Кефир 1,5% жирности': 25,
'Пекинская капуста сырая целая': 26,
'Конина мякоть сырая': 27,
'Крахмал картофельный': 28,
'Крекеры': 29,
'Кукурузная крупа': 30,
'Куриная грудка  сырая': 31,
'Куриное яйцо, сырое, с скорлупой': 32,
'Курица сырая': 33,
'Лавровый лист сушеный': 34,
'Лимон сырой целый': 35,
'Лист петрушки сырой целый': 36,
'Лук зеленый сырой': 37,
'Лук репчатый сырой целый': 38,
'Сочни из  пшеничной муки ': 39,
'Макаронные изделия с содержанием яиц, сухие': 40,
'Мандарин сырой целиком': 41,
'Манная крупа': 42,
'Масло сливочное': 43,
'Мед': 44,
'Молоко коровье 1,5% жирности безлактозное ультрапастеризованное (УВТ)': 45,
'Молоко коровье 1,5% жирности кипяченное': 46,
'Морковь сырая целая': 47,
'Мясо говядины сырое': 48,
'Овсяная крупа': 49,
'Огурец консервированный с жидкостью': 50,
'Огурец сырой целый': 51,
'Панировочные сухари': 52,
'Перловая крупа': 53,
'Пищевые дрожжи': 54,
'Плоды шиповника сушеные': 55,
'Подсолнечное масло': 56,
'Полутвердый сыр  не менее 40% жирности  ': 57,
'Природная минеральная вода': 58,
'Пшеничная крупа': 59,
'Пшеничная мука': 60,
'Пшено ': 61,
'Рис круглозерновой ': 62,
'Сахар белый': 63,
'Свекла сырая целая': 64,
'Сладкий перец сырой целый': 65,
'Сливки 30% жирности': 66,
'Сметана 20% жирности': 67,
'Судак филе сырой': 68,
'Сухие дрожжи': 69,
'Творог не менее 10% жирности': 70,
'Томатная паста': 71,
'Помидоры сырые целые': 72,
'Лаваш': 73,
'Тыква сырая целая': 74,
'Укроп сырой': 75,
'Фасоль белая сушеная': 76,
'Филе говядины сырое': 77,
'Цветная капуста сырая целая': 78,
'Ржаной хлеб ': 79,
'Ржано-пшеничный хлеб': 80,
'Черный чай сухой': 81,
'Чеснок сырой целый': 82,
'Щавель сырой': 83,
'Яблоко очищенное сушеное': 84,
'Яблоко сырое целое': 85,
'Яблочный сок (без добавления сахара)': 86,
'Ячневая крупа': 87,
'Сушки простые (Мука высшего сорта)': 88,
'Печенье Крокет': 89,
'Хлопья «Геркулес» (овес)': 90,
'Спагетти сухие': 91,
'Кукуруза консервированная ,без жидкости,': 92,
}
# Convert product names to ids
##############################################
# import pandas as pd

# # Load the Excel file
# file_path = 'weight_losses_with_ids.xlsx'  # Replace with the path to your Excel file
# df = pd.read_excel(file_path)

# # Assuming 'product_id_map' is your mapping dictionary
# # Example: product_id_map = {'ProductA': 'ID1', 'ProductB': 'ID2', ...}

# # Replace values in the first column based on the mapping
# first_column_name = df.columns[0]
# df[first_column_name] = df[first_column_name].apply(lambda x: factors_map.get(x, x))

# # Save the updated DataFrame back to an Excel file
# updated_file_path = 'weight_losses_with_ids2.xlsx'  # Specify the desired path for the updated file
# df.to_excel(updated_file_path, index=False)
# #############################################

import pandas as pd

# Load the Excel file
file_path = 'weight_losses.xlsx'  # Replace with the path to your Excel file
df = pd.read_excel(file_path)

# Preprocess mapping dictionary keys to be case and whitespace insensitive
product_id_map_insensitive = {k.lower().replace(" ", ""): v for k, v in product_id_map.items()}

# Function to standardize text for case and whitespace insensitivity
# Converts any input to a string before applying transformations
def standardize_text(text):
    text = str(text)  # Convert to string to ensure compatibility with non-string types
    return text.lower().replace(" ", "")

# Replace values in the first column based on the mapping
first_column_name = df.columns[0]
df[first_column_name] = df[first_column_name].apply(lambda x: product_id_map_insensitive.get(standardize_text(x), x))

# Save the updated DataFrame back to an Excel file
updated_file_path = 'weight_losses_with_ids2.xlsx'  # Specify the desired path for the updated file
df.to_excel(updated_file_path, index=False)
