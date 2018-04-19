# PHP-Registration-Sys
PHP-Registration-Sys is a PHP/JWT webapp to handle registration, login, and session validation. relies on MySQL resources database

  - Pure PHP source-code
  - Lightweight
    - Only two dependencies
    - All logic contained in one USER class file
  - Easy to use
    - Config.php contains all custom configurations
    - Header.php contains routing rules and handles url queries
  - Minimal scripting within views

### Dependencies & Techs

PHP-Registration-Sys employes a number of web and dev technologies to work properly:
* [Composer](https://getcomposer.org/) - Dependency Manager for PHP [v1.6.*]
* [PHPMailer](https://github.com/PHPMailer/PHPMailer) - The classic email sending library for PHP [v6.*]
* [lcobucci/jwt](https://github.com/lcobucci/jwt) - A simple PHP library to work with JSON Web Token and JSON Web Signature[v3.2.* !IMPORTANT]
* [Mailgun](https://www.mailgun.com/) - Powerful APIs that enable you to send receive and track email effortlessly
* [JWT](https://jwt.io/) - JWT official online tools

### Installation

Create a new directory in your www or htdocs folder and clone the repository in it. If not already included user "composer install" to auto-require the depencies stated above

```sh
$ mkdir NEW-FOLDER
$ cd NEW-FOLDER
$ git clone https://github.com/Samz851/PHP-Registration-Sys.git
$ composer install
```
### Usage
#### Config.php
```php
getHost();             // Returns Host name
getDBName();           // Returns name of Database
getDBUsername();       // Returns Username for Database
getDBPassword();       // Returns Password for Database
getSecretKey();        // Returns the secret key to be used in JWT signature
getServerName();       // Returns Name of Server
getMailHost();         // Returns Host Name for mail service
getMailUsername();     // Returns Username for mail service
getMailPassword();     // Returns Password for mail service
getFromEmail();        // Returns the email address to show as FROM
getReplyEmail();       // Returns the email address to show as Reply-To
getDomain();           // Returns the domain name
```
##### First
You need to locate Config.php and edit the custom configurations for your app. As well as import the included table schema to your sql database
##### Create an instance:
All important functions and methods needed for the app are contained within the class.user.php file.
Require the file assign an insance of USER class:
```php
require_once 'app/class.user.php';
$user = new USER();
```
##### Check Email
Checks if email already exists in Database
```php
$user->check_email($email);
```
##### Register
Sends data from view for validation and storage - activation email send internally through private methods (Please customize the url and message in class.user.php)
```php
$user->register($data);
```
##### Activation
Email sent contains randomly generated code and token in url query
```php
$user->activate_user($code, $token);
```
##### Login
Simple login method
```php
$user->login($input_email, $input_password);
```
##### Reset Email
To reset password simply enter email and call method
```php
$user->send_pass_reset($email);
```
##### Reset Password
Include the code contained in url query in the submission of input fields
```php
$user->update_password($new_pass, $code);
```
#### Extras
```php
$user->redirect(url);                   // Redircts user
$user->fetch_user_datatable($user)      // Takes user id and fetches all data from db
$user->retrieve_token_claim($claim)     // Takes a claim(string) and retrieves the claim from the JWT saved in cookie
$user->validate_token($str_token)       // Takes stringed JWT and validates it
$user->verify_token($str_token)         // Takes JWT string token and verifies it, return true or false
$user->set_cookie($token)               // stores the JWT in a cookie
$user->destroy_cookie($token)           // Deletes the cookie
```
