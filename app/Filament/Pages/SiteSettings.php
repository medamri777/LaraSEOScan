<?php

namespace App\Filament\Pages;

use App\Models\Plan;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Configuration';
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?int    $navigationSort  = 10;
    protected static string  $view           = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            // Branding
            'site_name'    => Setting::get('site_name', config('app.name')),
            'footer_text'  => Setting::get('footer_text', ''),
            // Defaults
            'default_trial_days' => Setting::get('default_trial_days', '14'),
            'default_plan'       => Setting::get('default_plan', 'free'),
            // Feature toggles
            'enable_ai_keywords'    => Setting::get('enable_ai_keywords', '1'),
            'enable_pagespeed'     => Setting::get('enable_pagespeed', '1'),
            'enable_gsc'           => Setting::get('enable_gsc', '1'),
            'enable_competitor_analysis' => Setting::get('enable_competitor_analysis', '1'),
            // API Keys (read from env, don't expose real values)
            'dataforseo_login'      => Setting::get('dataforseo_login', config('services.dataforseo.login', '')),
            'dataforseo_password'   => Setting::get('dataforseo_password', ''),
            'google_pagespeed_key'  => Setting::get('google_pagespeed_key', ''),
            'openpagerank_key'      => Setting::get('openpagerank_key', ''),
            'groq_api_key'          => Setting::get('groq_api_key', ''),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Branding')->schema([
                    Forms\Components\TextInput::make('site_name')
                        ->label('Site Name')
                        ->required()
                        ->maxLength(255),
                    Forms\Components\TextInput::make('footer_text')
                        ->label('Footer Text')
                        ->maxLength(500)
                        ->placeholder('e.g. (c) 2026 Seo4ma. All rights reserved.'),
                ])->columns(2),

                Forms\Components\Section::make('Defaults')->schema([
                    Forms\Components\TextInput::make('default_trial_days')
                        ->label('Default Trial Days')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(365)
                        ->default(14),
                    Forms\Components\Select::make('default_plan')
                        ->label('Default Plan for New Signups')
                        ->options(function () {
                            try {
                                return Plan::active()->pluck('name', 'slug')->toArray();
                            } catch (\Throwable) {
                                return ['free' => 'Free', 'pro' => 'Pro', 'guru' => 'Guru', 'business' => 'Business', 'agency' => 'Agency'];
                            }
                        })
                        ->default('free'),
                ])->columns(2),

                Forms\Components\Section::make('Feature Toggles')->schema([
                    Forms\Components\Toggle::make('enable_ai_keywords')
                        ->label('AI Keyword Generation'),
                    Forms\Components\Toggle::make('enable_pagespeed')
                        ->label('Google PageSpeed Insights'),
                    Forms\Components\Toggle::make('enable_gsc')
                        ->label('Google Search Console Integration'),
                    Forms\Components\Toggle::make('enable_competitor_analysis')
                        ->label('Competitor Analysis'),
                ])->columns(2),

                Forms\Components\Section::make('API Keys')
                    ->description('Update API credentials. Leave blank to keep existing .env values.')
                    ->schema([
                        Forms\Components\TextInput::make('dataforseo_login')
                            ->label('DataForSEO Login')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('dataforseo_password')
                            ->label('DataForSEO Password')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('google_pagespeed_key')
                            ->label('Google PageSpeed API Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('openpagerank_key')
                            ->label('OpenPageRank API Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('groq_api_key')
                            ->label('Groq AI API Key')
                            ->password()
                            ->revealable()
                            ->maxLength(255),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Branding
        Setting::set('site_name', $data['site_name'], 'branding');
        Setting::set('footer_text', $data['footer_text'] ?? '', 'branding');

        // Defaults
        Setting::set('default_trial_days', $data['default_trial_days'], 'defaults');
        Setting::set('default_plan', $data['default_plan'], 'defaults');

        // Features
        Setting::set('enable_ai_keywords', $data['enable_ai_keywords'] ? '1' : '0', 'features');
        Setting::set('enable_pagespeed', $data['enable_pagespeed'] ? '1' : '0', 'features');
        Setting::set('enable_gsc', $data['enable_gsc'] ? '1' : '0', 'features');
        Setting::set('enable_competitor_analysis', $data['enable_competitor_analysis'] ? '1' : '0', 'features');

        // API Keys (only save if provided)
        if (! empty($data['dataforseo_login'])) {
            Setting::set('dataforseo_login', $data['dataforseo_login'], 'api_keys');
        }
        if (! empty($data['dataforseo_password'])) {
            Setting::set('dataforseo_password', $data['dataforseo_password'], 'api_keys');
        }
        if (! empty($data['google_pagespeed_key'])) {
            Setting::set('google_pagespeed_key', $data['google_pagespeed_key'], 'api_keys');
        }
        if (! empty($data['openpagerank_key'])) {
            Setting::set('openpagerank_key', $data['openpagerank_key'], 'api_keys');
        }
        if (! empty($data['groq_api_key'])) {
            Setting::set('groq_api_key', $data['groq_api_key'], 'api_keys');
        }

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            \Filament\Actions\Action::make('save')
                ->label('Save Settings')
                ->submit('save'),
        ];
    }
}
