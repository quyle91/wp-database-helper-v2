1. Install 
composer require quyle91/wp-database-helper-v2:dev-main
composer update quyle91/wp-database-helper-v2 --prefer-source --no-cache



2. Khai báo Field

```php
$field = WpField::make('input', 'custom_text')
    ->label('Custom Text')
    ->attribute([
        'type' => 'text',
        'placeholder' => 'Enter text...',
    ])
    ->default('Hello world')
    ->show_in_admin(true);
```

3. Khai báo Repeater
```php
$nestedRepeater = WpRepeater::make('my_repeater')
    ->label('Level 1')
    ->fields([

        // Input 1
        WpField::make('text', 'input_1')
            ->label('Input 1'),

        // Repeater 1
        WpRepeater::make('repeater_1')
            ->label('Repeater 1')
            ->fields([

                WpField::make('text', 'input_1_1')
                    ->label('Input 1.1'),

                // Repeater 1.2
                WpRepeater::make('repeater_1_2')
                    ->label('Repeater 1.2')
                    ->fields([
                        WpField::make('text', 'input_1_2_1')
                            ->label('Input 1.2.1'),

                        WpField::make('text', 'input_1_2_2')
                            ->label('Input 1.2.2'),
                    ]),
            ]),
    ])
    ->default([
        [
            'input_1' => 'Hello',
            'repeater_1' => [
                [
                    'input_1_1' => 'Nested A',
                    'repeater_1_2' => [
                        [
                            'input_1_2_1' => 'Deep 1',
                            'input_1_2_2' => 'Deep 2',
                        ],
                    ],
                ],
            ],
        ],
    ]);
<div class="repeater" data-base="my_repeater">
  <div class="repeater-list">
    <div class="repeater-item" data-index="0">
      <input type="text" name="my_repeater[0][input_1]" value="Hello">

      <div class="repeater" data-base="my_repeater[0][repeater_1]">
        <div class="repeater-list">
          <div class="repeater-item" data-index="0">
            <input type="text" name="my_repeater[0][repeater_1][0][input_1_1]" value="Nested A">

            <div class="repeater" data-base="my_repeater[0][repeater_1][0][repeater_1_2]">
              <div class="repeater-list">
                <div class="repeater-item" data-index="0">
                  <input type="text" name="my_repeater[0][repeater_1][0][repeater_1_2][0][input_1_2_1]" value="Deep 1">
                  <input type="text" name="my_repeater[0][repeater_1][0][repeater_1_2][0][input_1_2_2]" value="Deep 2">
                  <button type="button" class="repeater-clone">Clone</button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <button type="button" class="repeater-clone">Clone</button>
      </div>
    </div>
  </div>
  <button type="button" class="repeater-clone">Clone</button>
</div>


$_POST['my_repeater'] = [
    [
        'input_1' => 'Hello',
        'repeater_1' => [
            [
                'input_1_1' => 'Nested A',
                'repeater_1_2' => [
                    [
                        'input_1_2_1' => 'Deep 1',
                        'input_1_2_2' => 'Deep 2',
                    ],
                ],
            ],
        ],
    ],
];
```

4. Khai báo Metabox (Post Meta)
```php
WpMeta::make('page')
    ->metabox('Page Settings')
    ->fields([

        WpField::make('text', 'custom_text')
            ->label('Text field')
            ->attribute(['placeholder' => 'Enter text']),

        WpField::make('textarea', 'custom_note')
            ->label('Note'),

        // Khai báo repeater trực tiếp
        WpRepeater::make('my_repeater')
            ->label('Extra Info')
            ->fields([
                WpField::make('text', 'title')
                    ->label('Title'),

                WpField::make('text', 'value')
                    ->label('Value'),
            ])
            ->default([
                ['title' => 'First', 'value' => 'Hello'],
                ['title' => 'Second', 'value' => 'World'],
            ]),
    ])
    ->admin_columns(true)
    ->quick_edit(true)
    ->register();

DB: meta_value
[
    ['title' => 'First', 'value' => 'Hello'],
    ['title' => 'Second', 'value' => 'World'],
]
```


5. Khai báo Taxonomy Meta
```php
WpMeta::make_taxonomy('category')
    ->metabox('Category Extra')
    ->fields([
        WpField::make('input', 'cat_extra')
            ->label('Extra Info')
            ->attribute(['type' => 'text']),
    ])
    ->admin_columns(true)
    ->register();
```


6. Khai báo Table
```php
WpDatabase::make('custom_table')
    ->menu_title('Custom Table')
    ->columns([
        DbColumn::id('id'),
        DbColumn::integer('post_id')->notNull(),
        DbColumn::string('extra_value', 255)->notNull(),
        DbColumn::datetime('created_at')->default('CURRENT_TIMESTAMP'),
        DbColumn::datetime('updated_at')->default('CURRENT_TIMESTAMP'),
    ])
    ->primaryKey('id')
    ->register();
```

7. Khai báo “Config Page”
```php
// Đây là option page cho admin nhập cấu hình
WpMeta::make('page')
    ->metabox('Field Builder Config')
    ->fields([
        WpRepeater::make('builder_fields')
            ->label('Form Fields')
            ->fields([
                WpField::make('select', 'type')
                    ->label('Field Type')
                    ->options([
                        'text'     => 'Text',
                        'textarea' => 'Textarea',
                        'repeater' => 'Repeater',
                    ]),

                WpField::make('text', 'name')
                    ->label('Field Name'),

                WpField::make('text', 'label')
                    ->label('Field Label'),

                // Cho phép nested repeater: field con
                WpRepeater::make('sub_fields')
                    ->label('Sub Fields (only if repeater)')
                    ->fields([
                        WpField::make('text', 'name')->label('Sub Field Name'),
                        WpField::make('text', 'label')->label('Sub Field Label'),
                    ]),
            ])
    ])
    ->register();

DB: 
[
    [
        'type'  => 'text',
        'name'  => 'custom_text',
        'label' => 'Custom Text',
        'sub_fields' => [],
    ],
    [
        'type'  => 'repeater',
        'name'  => 'my_repeater',
        'label' => 'My Repeater',
        'sub_fields' => [
            ['name' => 'title', 'label' => 'Title'],
            ['name' => 'value', 'label' => 'Value'],
        ]
    ]
]

PHP build fields:
$config = get_option('builder_fields');

$fields = [];

foreach ($config as $field) {
    switch ($field['type']) {
        case 'text':
            $fields[] = WpField::make('text', $field['name'])
                ->label($field['label']);
            break;

        case 'textarea':
            $fields[] = WpField::make('textarea', $field['name'])
                ->label($field['label']);
            break;

        case 'repeater':
            $subFields = [];
            foreach ($field['sub_fields'] as $sub) {
                $subFields[] = WpField::make('text', $sub['name'])
                    ->label($sub['label']);
            }

            $fields[] = WpRepeater::make($field['name'])
                ->label($field['label'])
                ->fields($subFields);
            break;
    }
}

WpMeta::make('post') // áp dụng cho post type
    ->metabox('Dynamic Fields')
    ->fields($fields)
    ->register();

```


100. Folder structure
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


101. Use example
```php
$bootstrap = new \WpDatabaseHelperV2\Bootstrap();
$bootstrap->init();
```