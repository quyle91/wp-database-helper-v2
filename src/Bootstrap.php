<?php

namespace WpDatabaseHelperV2;

class Bootstrap {
    public static function run() {
        (new self())->init();
    }

    public function init() {

        // metabox
        add_action('init', function () {

            // 
            \WpDatabaseHelperV2\Meta\WpMeta::make()
                ->post_type('page')
                ->metabox_label('Complex Settings')
                ->fields([

                    // Field cơ bản
                    \WpDatabaseHelperV2\Fields\WpField::make()
                        ->kind('input')
                        ->type('text')
                        ->name('___page_subtitle')
                        ->label('Subtitle')
                        ->attribute(['placeholder' => 'Enter subtitle'])
                        ->adminColumn(true)
                        ->default('This is default subtitle'),

                    // Repeater cấp 1: FAQ
                    \WpDatabaseHelperV2\Fields\WpRepeater::make()
                        ->name('___faq_list')
                        ->label('FAQ List')
                        ->direction('vertical')
                        ->fields([

                            \WpDatabaseHelperV2\Fields\WpField::make()
                                ->kind('input')
                                ->type('text')
                                ->name('___question')
                                ->label('Question')
                                ->default('Question'),

                            \WpDatabaseHelperV2\Fields\WpField::make()
                                ->kind('input')
                                ->type('text')
                                ->name('___answer')
                                ->label('Answer')
                                ->default('Answer'),

                            // Repeater cấp 2: Related links (default riêng)
                            \WpDatabaseHelperV2\Fields\WpRepeater::make()
                                ->name('___related_links')
                                ->label('Related Links')
                                ->direction('horizontal')
                                ->fields([

                                    \WpDatabaseHelperV2\Fields\WpField::make()
                                        ->kind('input')
                                        ->type('text')
                                        ->name('___link_title')
                                        ->label('Link Title')
                                        ->default('Link Title'),

                                    \WpDatabaseHelperV2\Fields\WpField::make()
                                        ->kind('input')
                                        ->type('text')
                                        ->name('___link_url')
                                        ->label('Link URL')
                                        ->default('Link URL'),
                                ])
                                ->default([
                                    [
                                        '___link_title' => 'Link Title',
                                        '___link_url'   => 'Link URL',
                                    ]
                                ]),
                        ])
                        ->default([
                            [
                                '___question' => 'How to contact us?',
                                '___answer'   => 'Use the form below.',
                                '___related_links' => [
                                    [
                                        '___link_title' => 'Docs',
                                        '___link_url' => 'https://docs.local',
                                    ],
                                    [
                                        '___link_title' => 'Docs 2',
                                        '___link_url' => 'https://docs.local2',
                                    ],
                                ],
                            ],
                            [
                                '___question' => 'What is this site?',
                                '___answer'   => 'This is a demo.',
                            ],
                        ]),
                ])
                ->register();

            // Field text bình thường
            add_action('wpdh_meta_box_after', function () {
                echo '<pre>';
                print_r('---------- TEST INPUT without save data ----------');
                echo '</pre>';
                echo \WpDatabaseHelperV2\Fields\WpField::make()
                    ->kind('input')
                    ->type('text')
                    ->value($this->settings['test_input'] ?? false) // giá trị đã lưu
                    ->name('adminz_admin[test_input]')
                    ->label('Test Input')
                    ->attribute(['placeholder' => 'Enter test'])
                    ->default('This is default test')
                    ->render();

                echo '<pre>';
                print_r('---------- TEST Repeater without save data ----------');
                echo '</pre>';
                echo \WpDatabaseHelperV2\Fields\WpRepeater::make()
                    ->name('adminz_admin[test_repeater]')
                    ->value($this->settings['test_repeater'] ?? false) // giá trị đã lưu
                    ->label('Test Repeater')
                    ->fields([
                        \WpDatabaseHelperV2\Fields\WpField::make()
                            ->kind('input')
                            ->type('text')
                            ->name('___test_name_')
                            ->label('Test')
                            ->default('This is default test')
                            ->attribute(['placeholder' => 'Enter test']),
                        \WpDatabaseHelperV2\Fields\WpField::make()
                            ->kind('input')
                            ->type('text')
                            ->name('___test_name_2')
                            ->label('Test 2')
                            ->default('This is default test 2')
                            ->attribute(['placeholder' => 'Enter test 2']),
                    ])
                    ->default([
                        [
                            '___test_name_' => 'This is default test',
                            '___test_name_2' => 'This is default test 2',
                        ],
                        [
                            '___test_name_' => 'This is default test x',
                            '___test_name_2' => 'This is default test 2 x',
                        ],
                    ])
                    ->render();
            }, 10, 2);
        });
    }
}
