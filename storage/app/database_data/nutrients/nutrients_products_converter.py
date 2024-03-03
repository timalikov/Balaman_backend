import pandas as pd
import os

# Load the Excel file
file_path = 'nutrients_products_with_ids.xlsx'  
path_to_save = "excel_data"
df = pd.read_excel(file_path)

# Get the first column name
first_column_name = df.columns[0]

# Iterate over the remaining columns and create new Excel files
file_counter = 1
for col in df.columns[1:]:
    # Select the first column and the current column
    df_subset = df[[first_column_name, col]]

    # Save this subset to a new Excel file
    new_file_name = f'{file_counter}.xlsx'

    if not os.path.exists(path_to_save):
        os.makedirs(path_to_save)

    df_subset.to_excel(os.path.join(path_to_save, new_file_name), index=False)


    # Increment the file counter
    file_counter += 1
