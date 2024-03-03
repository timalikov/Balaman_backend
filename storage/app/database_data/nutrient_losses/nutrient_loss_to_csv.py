import pandas as pd
import os

def percent_to_coefficient(value):
    """Convert a percent value to a coefficient."""
    try:
        if isinstance(value, str):
            percent_value = float(value.strip('%'))
        elif isinstance(value, (float, int)):
            percent_value = value
        else:
            return value
        return round(1 - percent_value / 100, 2)
    except ValueError:
        return value

path = "excel_data/"
path_to_save = "csv_data/"
if not os.path.exists(path_to_save):
    os.makedirs(path_to_save)

for i in range(1, 27):
    file_path = os.path.join(path, f'{i}.xlsx')
    try:
        df = pd.read_excel(file_path)
    except FileNotFoundError:
        print(f"File not found: {file_path}")
        continue

    if len(df.columns) >= 3:
        third_column_header = df.columns[2]
        third_column_copy = df[third_column_header].copy()
        df[third_column_header] = third_column_header
        df[str(third_column_header) + '_copy'] = third_column_copy
        df.columns = ['product_id', 'factor_id', 'nutrient_id', 'coefficient'] + df.columns.tolist()[4:]

        third_column_name = df.columns[3]
        df[third_column_name] = df[third_column_name].apply(percent_to_coefficient)

        updated_file_path = os.path.join(path_to_save, f'{i}.csv')
        df.to_csv(updated_file_path, index=False)
    else:
        print(f"DataFrame in {file_path} does not have enough columns to perform the operation.")
