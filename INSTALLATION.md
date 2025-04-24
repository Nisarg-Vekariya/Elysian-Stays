
# 🛠️ Installation Guide – Elysian Stays

Follow the steps below to install and run the **Elysian Stays** hotel booking platform locally.

---

## 📥 Step 1: Clone the Repository

```bash
git clone https://github.com/your-username/elysian-stays.git
cd elysian-stays
```

---

## 🗃️ Step 2: Import the Database

1. Open **phpMyAdmin** or your MySQL client.
2. Create a new database named:

```
elysian_stays
```

3. Import the provided SQL file located at the root of the project:

```
elysian_stays.sql
```

---

## ⚙️ Step 3: Configure the Environment

Update your database configuration in `config/db.php`:

```php
$host = 'localhost';
$dbname = 'elysian_stays';
$user = 'root';
$pass = '';
```

Set up your Stripe keys and email credentials:

```php
// config/stripe.php
$stripeSecretKey = 'sk_test_your_secret_key';
$stripePublishableKey = 'pk_test_your_publishable_key';

// config/mail.php
$mailHost = 'smtp.example.com';
$mailUsername = 'your@email.com';
$mailPassword = 'your_email_password';
```

---

## 📦 Step 4: Install Dependencies

Install **PHPMailer** and **Stripe PHP SDK** using Composer:

```bash
composer require phpmailer/phpmailer
composer require stripe/stripe-php
```

Alternatively, manually place downloaded packages inside the `vendor/` folder.

---

## 🚀 Step 5: Run the Project

1. Place the project inside your local web server directory (e.g., `htdocs` if using XAMPP).
2. Start Apache and MySQL.
3. Open your browser and navigate to:

```
http://localhost/elysian-stays
```

---

You're now ready to explore Elysian Stays locally! For advanced configuration or deployment, see the full README.
