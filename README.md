# Fastpanel.php
Unofficial Fastpanel API written in PHP

The project is not completely finished and there may be bugs, but most of the api features have been implemented.
It was developed in the summer of 2023 and distributed among my friends, but I decided to publish it on github.

## Install
```bash
git clone https://github.com/atorisen/fastpanel-php.git
```

## Usage
```php
require "fastpanel.php";

$panel = new fastpanel("panel.example.com");

$panel->login("login", "password");

$account_info = $panel->account_information();

$created_user = $panel->create_user("username", "password", "ROLE_USER", 10240);

$panel->delete_user($created_user['id']);
```
