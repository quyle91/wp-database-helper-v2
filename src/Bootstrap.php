<?php

namespace WpDatabaseHelperV2;

use \WpDatabaseHelperV2\Meta\WpMeta;
use \WpDatabaseHelperV2\Fields\WpField;
use \WpDatabaseHelperV2\Fields\WpRepeater;

class Bootstrap {
    public static function run() {
        (new self())->init();
    }

    public function init() {
        // tạo assets service, đăng ký (register) sẵn
        \WpDatabaseHelperV2\Services\Assets::get_instance();

        add_action('init', function () {

            WpMeta::make('page')
                ->metabox('Complex Settings')
                ->fields([

                    // Field cơ bản
                    WpField::make('text', '___page_subtitle')
                        ->label('Subtitle')
                        ->attribute(['placeholder' => 'Enter subtitle'])
                        ->default('This is default subtitle')
                        ->adminColumn(true),

                    // Repeater cấp 1: FAQ
                    WpRepeater::make('___faq_list')
                        ->label('FAQ List')
                        ->fields([

                            WpField::make('text', '___question')
                                ->label('Question')
                                ->default('Question'),

                            WpField::make('textarea', '___answer')
                                ->label('Answer')
                                ->default('Answer'),

                            // Repeater cấp 2: Related links (default riêng)
                            WpRepeater::make('___related_links')
                                ->label('Related Links')
                                ->fields([

                                    WpField::make('text', '___link_title')
                                        ->label('Link Title')
                                        ->default('Link Title'),

                                    WpField::make('text', '___link_url')
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
                                '___question' => 'What is this site?',
                                '___answer'   => 'This is a demo.',
                            ],
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
                        ]),
                ])
                ->register();
        });
    }
}
