import pandas as pd
import os

path = "excel_data/"
path_to_save = "csv_data/"
if not os.path.exists(path_to_save):
    os.makedirs(path_to_save)

for i in range(1, 697):
    file_path = f'{path}{i}.xlsx'  # Replace with the path to your original Excel file

    df = pd.read_excel(file_path)

    # Check if the DataFrame has at least two columns
    if len(df.columns) >= 2:
        # Store the header (first value) of the original second column
        second_column_header = df.columns[1]

        # Make a copy of the second column
        second_column_copy = df[second_column_header].copy()

        # Replace the second column with the header name of the original second column
        df[second_column_header] = second_column_header

        # Append the copied column as a new column (the last column) of the DataFrame
        df[str(second_column_header) + '_copy'] = second_column_copy

       # Rename the headers of the first, second, and third columns
        if len(df.columns) >= 3:
            df.columns = ['micro_id', 'product_id', 'weight'] + df.columns.tolist()[3:]
    else:
        print("DataFrame does not have enough columns to perform the operation.")

    # Save the modified DataFrame to a CSV file
    updated_file_path = f'{path_to_save}{i}.csv'
    df.to_csv(updated_file_path, index=False)
