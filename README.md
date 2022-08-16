# Laravel Lex Chatbot

Dead simple chatbot app which leverages Amazon LEX using:
- Laravel 9
- Vue 3
- Vite 3
- AWS PHP SDK

(TL,DR: it uses the latest tech stack)

### Installation

1. Clone this repo
2. Fill in your DB credentials and AWS credentials on these following properties on `.env` file:
- `AWS_ACCESS_KEY_ID`
- `AWS_SECRET_ACCESS_KEY`
- `AWS_DEFAULT_REGION`
3. Run `composer install && npm install`
4. Run `php artisan migrate`

### Running the app

Run `npm run dev`
