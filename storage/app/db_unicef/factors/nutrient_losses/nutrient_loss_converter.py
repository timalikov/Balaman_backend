import pandas as pd
import os


# Load the Excel file
file_path = 'nutrient_losses_with_ids_new.xlsx'
path_to_save = "excel_data"

# Create the directory if it doesn't exist
if not os.path.exists(path_to_save):
    os.makedirs(path_to_save)

df = pd.read_excel(file_path)

# Get the first two column names
first_two_column_names = df.columns[:2]

# Iterate over the remaining columns and create new Excel files
file_counter = 1
for col in df.columns[2:]:
    # Select the first two columns and the current column
    df_subset = df[list(first_two_column_names) + [col]]

    # Save this subset to a new Excel file
    new_file_name = f'{file_counter}.xlsx'
    df_subset.to_excel(os.path.join(path_to_save, new_file_name), index=False)

    # Increment the file counter
    file_counter += 1
