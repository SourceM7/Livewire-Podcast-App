## Installation

1. Clone and install dependencies:
   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. Configure your database in `.env`

3. Run setup commands:
   ```bash
   php artisan migrate
   php artisan reverb:start & 
   php artisan queue:work &   
   npm run dev
   php artisan serve
   ```

4. Open `http://127.0.0.1:8000` and add your RSS feed URL
