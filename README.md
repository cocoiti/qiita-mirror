# qiita-mirror
simple qiita-team-mirror site

## Install

```
composer install
cd app/config/
cp config.php.sample config.php
vi config.php

cd ../../
app/console dump

cd web/
php -S localhost:10080
```

and access web/index.php


## requirement

PHP 5.5 or later

