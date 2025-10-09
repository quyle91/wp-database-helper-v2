0. Install 
composer require quyle91/wp-database-helper-v2:dev-main
composer update quyle91/wp-database-helper-v2 --prefer-source --no-cache

1. Folder structure
wp-database-helper-v2/
├─ composer.json
├─ README.md
├─ assets/
│  ├─ css/
│  │  └─ repeater.css
│  ├─ js/
│  │  └─ repeater.js
├─ src/
│  ├─ Bootstrap.php          # init composer bindings, service container (simple)
│  ├─ Services/
│  │  └─ Renderer.php
│  ├─ Fields/
│  │  ├─ WpField.php
│  │  └─ WpRepeater.php
│  ├─ Meta/
│  │  └─ WpMeta.php
│  ├─ Database/
│  │  └─ DbColumn.php
│  └─ Helpers/
│     └─ Arr.php
├─ views/
│  └─ fields/
│     ├─ field-text.php
└─    └─ field-repeater.php

2. Use example
```php
$bootstrap = new \WpDatabaseHelperV2\Bootstrap();
$bootstrap->init();
```

