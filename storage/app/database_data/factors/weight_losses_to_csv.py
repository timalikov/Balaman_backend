import pandas as pd
import os

def percent_to_coefficient(value):
    """Convert a percent value to a coefficient."""
    try:
        if isinstance(value, str):
            # If the value is a string, remove the '%' sign and convert to float
            percent_value = float(value.strip('%'))
        elif isinstance(value, (float, int)):
            # If the value is already a number, use it as is
            percent_value = value
        else:
            # Return the original value if it's neither string nor numeric
            return value

        return round(1 + percent_value / 100, 2)
    except ValueError:
        # Return the original value if conversion is not possible
        return value

path = "excel_data/"
path_to_save = "csv_data/"
if not os.path.exists(path_to_save):
    os.makedirs(path_to_save)

for i in range(1, 10):
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
            df.columns = ['product_id', 'factor_id', 'coefficient'] + df.columns.tolist()[3:]
    else:
        print("DataFrame does not have enough columns to perform the operation.")

    # Convert the third column values from percent to coefficient
    third_column_name = df.columns[2]
    df[third_column_name] = df[third_column_name].apply(percent_to_coefficient)

    # Save the modified DataFrame to a CSV file
    updated_file_path = f'{path_to_save}{i}.csv'
    df.to_csv(updated_file_path, index=False)
