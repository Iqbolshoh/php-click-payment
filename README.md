# PHP-Click-Payment

This repository contains a simple and efficient implementation for handling payments using the Click payment system. The project is designed for integration into web-based applications and includes essential files for configuration and operation.

![Payment Workflow](images/payment.png)

## 📂 Project Structure

- **📄 README.md**: Documentation file providing an overview of the project.
- **💻 complete.php**: Script to handle payment completion logic.
- **⚙️ config.php**: Configuration file for payment information and database connection.
- **🗄️ database.sql**: SQL file for setting up the database structure required for the project.
- **🌐 index.php**: Provides the user with a button to make a payment with the CLICK payment system.
- **🛠️ prepare.php**: Script to handle initial payment preparation logic.
- **🔄 redirect_payment.php**: To be redirected to the payment page.

## Interaction Description

Interaction with the system is performed via the API interface on the provider’s server. The API interface must fully comply with the specifications outlined below. Payments created in the CLICK system are transmitted over HTTP (HTTPS) via POST requests. The supplier must provide CLICK URL addresses to the system for API communication.

Interaction consists of two stages:

1. **Prepare**
2. **Complete**

 ---

## Stage 1: Prepare (Action = 0)

**Request Parameters:**

| #  | Parameter Name      | Data Type  | Description                                                                                   |
|----|---------------------|------------|-----------------------------------------------------------------------------------------------|
| 1  | click_trans_id      | bigint     | ID of transaction in CLICK system.                                                           |
| 2  | service_id          | int        | ID of the service.                                                                            |
| 3  | click_paydoc_id     | bigint     | Payment ID in CLICK system. Displayed to the customer in SMS when paying.                    |
| 4  | merchant_trans_id   | varchar    | Order ID/personal account/login in the supplier’s billing system.                            |
| 5  | amount              | float      | Payment amount (in soums).                                                                   |
| 6  | action              | int        | Action to perform: 0 for Prepare.                                                            |
| 7  | error               | int        | Status code about completion of payment. 0 for success, error code otherwise.               |
| 8  | error_note          | varchar    | Description of the status code.                                                              |
| 9  | sign_time           | varchar    | Payment date in the format 'YYYY-MM-DD HH:mm:ss'.                                            |
| 10 | sign_string         | varchar    | MD5 hash confirming the authenticity of the request.                                         |

**Response Parameters:**

| #  | Parameter Name        | Data Type  | Description                                                                                   |
|----|-----------------------|------------|-----------------------------------------------------------------------------------------------|
| 1  | click_trans_id        | bigint     | Payment ID in CLICK system.                                                                  |
| 2  | merchant_trans_id     | varchar    | Order ID/personal account/login in the supplier’s billing system.                            |
| 3  | merchant_prepare_id   | int        | Payment ID in the supplier's billing system.                                                 |
| 4  | error                 | int        | Status code about completion of payment. 0 for success, error code otherwise.               |
| 5  | error_note            | varchar    | Description of the status code.                                                              |

The supplier verifies the payment options (merchant_trans_id, amount) to ensure validity and capability to provide the service or product.

---

## Stage 2: Complete (Action = 1)

**Request Parameters:**

| #  | Parameter Name        | Data Type  | Description                                                                                   |
|----|-----------------------|------------|-----------------------------------------------------------------------------------------------|
| 1  | click_trans_id        | bigint     | Payment ID in CLICK system.                                                                  |
| 2  | service_id            | int        | ID of the service.                                                                            |
| 3  | click_paydoc_id       | bigint     | Payment number in CLICK system. Displayed to the customer in SMS when paying.                |
| 4  | merchant_trans_id     | varchar    | Order ID/personal account/login in the supplier’s billing system.                            |
| 5  | merchant_prepare_id   | int        | Payment ID from the "Prepare" stage.                                                        |
| 6  | amount                | float      | Payment amount (in soums).                                                                   |
| 7  | action                | int        | Action to perform: 1 for Complete.                                                          |
| 8  | error                 | int        | Status code about completion of payment. 0 for success, error code otherwise.               |
| 9  | error_note            | varchar    | Description of the status code.                                                              |
| 10 | sign_time             | varchar    | Payment date in the format 'YYYY-MM-DD HH:mm:ss'.                                            |
| 11 | sign_string           | varchar    | MD5 hash confirming the authenticity of the request.                                         |

**Response Parameters:**

| #  | Parameter Name        | Data Type  | Description                                                                                   |
|----|-----------------------|------------|-----------------------------------------------------------------------------------------------|
| 1  | click_trans_id        | bigint     | Payment ID in CLICK system.                                                                  |
| 2  | merchant_trans_id     | varchar    | Order ID/personal account/login in the supplier’s billing system.                            |
| 3  | merchant_confirm_id   | int        | Transaction ID to complete the payment in the billing system. May be NULL.                   |
| 4  | error                 | int        | Status code about completion of payment. 0 for success, error code otherwise.               |
| 5  | error_note            | varchar    | Description of the status code.                                                              |

---

## Database Structure

```sql
CREATE DATABASE IF NOT EXISTS payment;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    amount DECIMAL(10, 2) NOT NULL,
    click_trans_id VARCHAR(100) NOT NULL,
    merchant_trans_id VARCHAR(100) NOT NULL,
    status ENUM('unpay', 'paid') DEFAULT 'unpay',
    time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

This structure can be modified to suit your application's specific requirements. Ensure all payments are properly recorded in this table to track transactions.


## Basic parameters

```php
<?php
// Database connection constants
define("DB_SERVER", "YOUR_DB_SERVER");
define("DB_USERNAME", "YOUR_DB_USERNAME");
define("DB_PASSWORD", "YOUR_DB_PASSWORD");
define("DB_NAME", "payment");

// Click payment integration constants
define("MERCHANT_ID", "YOUR_MERCHANT_ID");
define("SERVICE_ID", "YOUR_SERVICE_ID");
define("MERCHANT_USER_ID", "YOUR_MERCHANT_USER_ID");
define("SECRET_KEY", "YOUR_SECRET_KEY");
```
Fill in your information to connect to the database and Click system.

---

## Features

- Secure integration with Click payment.
- Simple database setup and configuration.
- Modular scripts for preparation, redirection, and completion.

## Technologies Used
<div style="display: flex; flex-wrap: wrap; gap: 5px;">
    <img src="https://img.shields.io/badge/PHP-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
    <img src="https://img.shields.io/badge/MySQL-%234479A1.svg?style=for-the-badge&logo=mysql&logoColor=white"
        alt="MySQL">
</div>

## Contributing

Contributions are welcome! If you have suggestions or want to enhance the project, feel free to fork the repository and
submit a pull request.


## Connect with Me

I love connecting with new people and exploring new opportunities. Feel free to reach out to me through any of the
platforms below:

<table>
  <tr>
    <td>
      <a href="https://github.com/iqbolshoh">
        <img
          src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/github.svg"
          height="48" width="48" alt="GitHub" />
      </a>
    </td>
    <td>
      <a href="https://t.me/iqbolshoh_777">
        <img
          src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/telegram.svg"
          height="48" width="48" alt="Telegram" />
      </a>
    </td>
    <td>
      <a href="https://www.linkedin.com/in/iiqbolshoh/">
        <img
          src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/linkedin.svg"
          height="48" width="48" alt="LinkedIn" />
      </a>
    </td>
    <td>
      <a href="https://instagram.com/iqbolshoh_777" target="blank"><img align="center"
          src="https://raw.githubusercontent.com/rahuldkjain/github-profile-readme-generator/master/src/images/icons/Social/instagram.svg"
          alt="instagram" height="48" width="48" /></a>
    </td>
    <td>
      <a href="https://wa.me/qr/22PVFQSMQQX4F1">
        <img
          src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/whatsapp.svg"
          height="48" width="48" alt="WhatsApp" />
      </a>
    </td>
    <td>
      <a href="https://x.com/iqbolshoh_777">
        <img src="https://img.shields.io/badge/X-000000?style=for-the-badge&logo=x&logoColor=white" height="48"
          width="48" alt="Twitter" />
      </a>
    </td>
    <td>
      <a href="mailto:iilhomjonov777@gmail.com">
        <img src="https://github.com/gayanvoice/github-active-users-monitor/blob/master/public/images/icons/gmail.svg"
          height="48" width="48" alt="Email" />
      </a>
    </td>
  </tr>
</table>
