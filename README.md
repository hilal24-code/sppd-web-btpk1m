# SPPD Web BTPK1M

Web application for managing Surat Perintah Perjalanan Dinas (SPPD), built with CodeIgniter 4.

## Features

- Authentication and user management
- Employee and agency management
- Official travel assignment management
- Travel documents and printable reports

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL or MariaDB

## Installation

1. Clone the repository:

   ```bash
   git clone https://github.com/hilal24-code/sppd-web-btpk1m.git
   cd sppd-web-btpk1m
   ```

2. Install dependencies:

   ```bash
   composer install
   ```

3. Copy the environment file:

   ```bash
   cp env .env
   ```

4. Configure your database credentials and `app.baseURL` in `.env`.

5. Run database migrations:

   ```bash
   php spark migrate
   ```

6. Start the development server:

   ```bash
   php spark serve
   ```

7. Open http://localhost:8080 in your browser.

## Built With

- [CodeIgniter 4](https://codeigniter.com/)
- PHP
- MySQL
