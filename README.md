# MockTest Application

A simple PHP mock test application for XAMPP.

## Features

- Upload previous year questions via CSV
- Store uploaded questions in a local SQLite database
- Generate a randomized mock test
- Grade answers and display results immediately

## Deployment

1. Copy the `mocktest` directory to your XAMPP `htdocs` folder:
   - `C:\xampp\htdocs\mocktest`
2. Ensure the `data` folder is writable by the web server.
3. Open your browser and visit:
   - `http://localhost/mocktest/`
4. Upload questions using the `Upload Questions` page.
5. Start the mock test from the `Take MockTest` page.

## Database configuration

The app uses `config.php` to choose between SQLite and MySQL.

- `db_driver`: `sqlite` or `mysql`
- `sqlite_path`: path for SQLite database file
- `mysql_host`, `mysql_db`, `mysql_user`, `mysql_pass`: MySQL connection settings

For MySQL use, create a database named `mocktest` in XAMPP and update `config.php`.

## CSV Upload Format

Use the provided `questions-template.csv` file. The CSV must contain six columns:

- `Question`
- `Option 1`
- `Option 2`
- `Option 3`
- `Option 4`
- `Answer`

The `Answer` must exactly match one of the four options.

## Notes

- If you need to reset stored questions, remove `data/questions.db`.
- The app uses SQLite, so no separate database server is required.
