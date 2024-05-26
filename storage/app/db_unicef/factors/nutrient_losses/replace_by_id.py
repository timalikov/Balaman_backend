products_map = {'Абрикос сушеный (курага)': 1,
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
'Крахмал': 28,
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
'Творог не менее  10% жирности   ': 70,
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





#############################################
factors_map = {'Холодная обработка':1,
               'жарка на сковороде с маслом':2,
               'тушение':3,
               'приготовление на пару':4,
               'варка':5,
               'варка запекание':6,
               'запекание':6,
               'Запекание':6,
               'варка + запекание':6,
               'жарка на сковороде без масла':7,
               'жарка на сковроде без масла':7,
               'медленная варка':8,
               'варка на медленном огне':8,
               'Бланширование':9,
               }

# Convert product names to ids
##############################################
import pandas as pd

# Load the Excel file
file_path = 'nutrient_losses.xlsx'  # Replace with the path to your Excel file
df = pd.read_excel(file_path)

# Assuming 'product_id_map' is your mapping dictionary
# Example: product_id_map = {'ProductA': 'ID1', 'ProductB': 'ID2', ...}

# Replace values in the first column based on the mapping
first_column_name = df.columns[1]
df[first_column_name] = df[first_column_name].apply(lambda x: factors_map.get(x, x))


# Replace values in the first column based on the mapping
products_column = df.columns[0]
df[products_column] = df[products_column].apply(lambda x: products_map.get(x, x))


# Replace this with your actual mapping
header_id_mapping = {
  'Вода': 1,
  'Б': 2,
  'Ж': 3,
  'У': 4,
  'Органические кислоты': 5,
  'Витамин А, IU': 6,
  'Кальциферолы витамина D': 7,
  'Эквивалент альфа-токоферола витамина Е': 8,
  'Филлохинон витамина К': 9,
  'ТиаминB1': 10,
  'Рибофлавин': 11,
  'Ниацин': 12,
  'Витамин B5 пантотеновая кислота': 13,
  'Витамин В 6': 14,
  'Витамин B7, биотин (витамин H)': 15,
  'Фолиевая кислота': 16,
  'Витамин В 12': 17,
  'Витамин С, аскорбиновая кислота': 18,
  'Пищевые волокна': 19,
  'Калий, К': 20,
  'Кальций, Са': 21,
  'Магний,Mg': 22,
  'Фосфор, Р': 23,
  'Хлорид': 24,
  'Железо, Fe': 25,
  'Цинк, Zn': 26,
  'Медь, Cu': 27,
  'Марганец': 28,
  'Йодид': 29,
  'Сахароза (свекольный сахар)': 30,
  'Натрий, Na': 31,
  'Сахар (общий)': 32,
  'Водорастворимая клетчатка': 33,
  'Нерастворимая в воде клетчатка': 34,
  'Изолейцин': 35,
  'Лейцин': 36,
  'Лизин': 37,
  'Метионин': 38,
  'Цистеин': 39,
  'Фенилаланин': 40,
  'Тирозин': 41,
  'Треонин': 42,
  'Триптофан': 43,
  'Валин': 44,
  'Аргинин': 45,
  'Гистидин': 46,
  'Незаменимые аминокислоты': 47,
  'Аланин': 48,
  'Аспарагиновая кислота': 49,
  'Глутаминовая кислота': 50,
  'Глицин': 51,
  'Пролин': 52,
  'Серин': 53,
  'Насыщенные жирные кислоты': 54,
  'Мононенасыщенные жирные кислоты': 55,
  'Октадекатриеновая кислота / линоленовая кислота': 56,
  'Эйкозапентаеновая кислота': 57,
  'Докозагексаеновая кислота': 58,
  'Полиненасыщенные жирные кислоты': 59,
  'Омега-3 жирные кислоты': 60,
  'Омега-6 жирные кислоты': 61,
  'Холестерин': 62,
  'Соотношение полиненасыщенных и насыщенных жиров (P / S)': 63,
  'Хлебные единицы': 64,
  'Поваренная соль всего': 65,
  'Селен': 66,
  'TFA, трансжирные кислоты': 67,
  'Бета-криптоксантин': 68,
  'Лютеин + зеаксантин': 69,
  'Ликопин': 70
}



# Replace DataFrame headers with IDs from the mapping if they are in the mapping
df.columns = [header_id_mapping.get(col, col) for col in df.columns]

# Save the modified DataFrame to a new Excel file
new_file_path = 'nutrient_losses_with_ids_new.xlsx'
df.to_excel(new_file_path, index=False)

