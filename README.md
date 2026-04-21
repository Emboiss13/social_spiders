# Social Spiders

Widespread adoption of social media among the younger generation has created an environment where users share a large amount of their life online. 

This often leads users to inadvertently share personal data that can be used by threat actors for nefarious means, such as financial fraud. 

Social Spiders is an accessible web application that uses social media APIs to gather a users posts, and a NLP model to demonstrate where this data has been included in the users posts. 

## Frontend Development

We started the frontend dev cycle by making a prototype of the website on figma: [link](https://www.figma.com/design/OXL91Mlxzjwr6hEDrz8m5M/Social-Spiders?node-id=29-347&t=cxeVtbtDtA2j5mmW-1)


## Backend Setup Instructions


### 1️⃣ Linux

To set up Social Spiders on Linux:

1. Install `apache2` , `PHP 8.1` and **all** other web dependencies.
2. Run:
    ```bash
    sudo apt find / -name "libphp*"
    ```
3. Move the `libphpX.X.so` file to `usr/lib/apache2/modules/`
4. Edit `httpd.conf` by adding:
    ```xml
    <FilesMatch \.php$>
        SetHandler application/x-http-php
    </FilesMatch>
    ```
5. Restart apache (in the terminal run `services restart httpd`)
6. From the project root, install PHP dependencies with `composer install`
7. Deploy the app files and Composer `vendor/` directory into `/var/www/html/`
7. Navigate to http://127.0.0.1/ to confirm the web server is running and serving the correct services 


<br>


### 2️⃣ macOS

To set up Social Spiders on macOS using Homebrew (open your mac terminal or the VSCode built in terminal):

1. Install [Homebrew](https://brew.sh/) (if not installed):
   ```bash
   /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"
   ```
2. Install [Apache](https://www.git-tower.com/blog/apache-on-macos) `httpd` and `PHP 8.1`:
    ```bash
    brew install httpd php@8.1
    ```
3. Start the services:
    ```bash
    brew services start httpd
    brew services start php@8.1
    ```
4. Configure Apache for PHP:
    - **STEP 1**: In your mac terminal run `nano /opt/homebrew/etc/httpd/httpd.conf`. This will open `httpd.conf` and allow you to edit it.

    - **STEP 2**: Use `ctrl+W` to search for `LoadModule`. 
        Make sure the following are **enabled** by removing the `#` which is commenting them out: 
        - `LoadModule proxy_module lib/httpd/modules/mod_proxy.so`
        - `LoadModule proxy_fcgi_module lib/httpd/modules/mod_proxy_fcgi.so`

    - **STEP 3**: Right after the last `LoadModule` line add the following:
        ```bash
        <FilesMatch \.php$>
            SetHandler "proxy:fcgi://127.0.0.1:9000"
        </FilesMatch>
        ```
    - **STEP 4**: Restart apache with command `brew services restart httpd`
5. After saving, test the config:
    ```bash
    httpd -t
    ```
6. From the project root, install PHP dependencies:
    ```bash
    composer install
    ```
    Or 
    ```bash
    composer update
    ```
    



7. Deploy the app files and Composer `vendor/` directory to `DocumentRoot`:
    ```bash
    cp -r src/* /opt/homebrew/var/www/
    cp -r vendor /opt/homebrew/var/www/
    ```
8. Test the setup by either:
    - Opening the browser on `http://127.0.0.1:8080/`
    - Create `info.php` in `DocumentRoot` with `<?php phpinfo(); ?>` and visit it to verify PHP is running the server
<br>

---

#### 😵 macOS Troubleshooting

If you get this error: 

```bash
Bootstrap failed: 5: Input/output error
Try re-running the command as root for richer errors.
```

Make sure you are not already running `httpd`, try any of the following:

1. Stop and restart:
```bash
brew services stop httpd
brew services stop --all
brew services run httpd
brew services restart httpd
```

2. Clean files:
```bash
rm -f /opt/homebrew/var/run/httpd*.pid
```
---

<br>

## Dependencies

- pip install gliner

#### Web dependencies:
- httpd (apache2)
- php >=8.1
- php-curl (included in php@8.1)
- libapache2-mod-php

#### API dependencies:
- `Abraham/TwitterOAuth`
- `Espresso-dev/instagram-basic-display-php`
- `viktorruskai/facebook-graph-sdk`

#### Create `venv` for `Python` dependencies:
```bash
$ python3 -m venv venv
$ pip install gliner transformers
```

---
---

Original git env: [link](https://git.cardiff.ac.uk/c22005835/social-spiders)
