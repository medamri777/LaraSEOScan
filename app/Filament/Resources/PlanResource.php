<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    protected static ?string $model           = Plan::class;
    protected static ?string $navigationIcon  = 'heroicon-o-credit-card';
    protected static ?string $navigationGroup = 'Platform';
    protected static ?string $navigationLabel = 'Plans & Pricing';
    protected static ?int    $navigationSort  = 0;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identity')->schema([
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50)
                    ->helperText('e.g. free, pro, guru, business, agency'),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(100),
                Forms\Components\Textarea::make('description')
                    ->rows(2)
                    ->maxLength(500)
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Pricing')->schema([
                Forms\Components\TextInput::make('price_monthly')
                    ->label('Monthly Price ($)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->nullable()
                    ->helperText('Leave empty for free plans'),
                Forms\Components\TextInput::make('price_yearly')
                    ->label('Yearly Price ($)')
                    ->numeric()
                    ->minValue(0)
                    ->step(0.01)
                    ->nullable(),
                Forms\Components\TextInput::make('trial_days')
                    ->label('Trial Days')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(365)
                    ->default(14),
            ])->columns(3),

            Forms\Components\Section::make('Usage Limits')->description('Leave blank for unlimited')->schema([
                Forms\Components\TextInput::make('projects_limit')
                    ->label('Projects')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('keywords_limit')
                    ->label('Keywords')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('competitors_limit')
                    ->label('Competitors')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('scans_per_day')
                    ->label('Scans / Day')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('crawl_pages_limit')
                    ->label('Crawl Pages')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
                Forms\Components\TextInput::make('ai_credits_limit')
                    ->label('AI Credits')
                    ->numeric()
                    ->minValue(0)
                    ->nullable(),
            ])->columns(3),

            Forms\Components\Section::make('Enabled Features')->schema([
                Forms\Components\CheckboxList::make('features')
                    ->options(collect(Plan::allToolSlugs())->mapWithKeys(fn ($slug) => [
                        $slug => str_replace('_', ' ', ucfirst($slug)),
                    ])->toArray())
                    ->columns(3)
                    ->helperText('Select which tools are available on this plan.'),
            ]),

            Forms\Components\Section::make('Status')->schema([
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make('sort_order')
                    ->label('Display Order')
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower number appears first'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable()
                    ->width(50),
                Tables\Columns\TextColumn::make('slug')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free'     => 'gray',
                        'pro'      => 'primary',
                        'guru'     => 'warning',
                        'business' => 'danger',
                        'agency'   => 'success',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_monthly')
                    ->label('$/Month')
                    ->formatStateUsing(fn (?string $state) => $state ? "$" . number_format((float) $state, 2) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_yearly')
                    ->label('$/Year')
                    ->formatStateUsing(fn (?string $state) => $state ? "$" . number_format((float) $state, 2) : '—')
                    ->sortable(),
                Tables\Columns\TextColumn::make('scans_per_day')
                    ->label('Scans/Day')
                    ->formatStateUsing(fn (?int $state) => $state === null ? 'Unlimited' : $state)
                    ->sortable(),
                Tables\Columns\TextColumn::make('projects_limit')
                    ->label('Projects')
                    ->formatStateUsing(fn (?int $state) => $state === null ? 'Unlimited' : $state),
                Tables\Columns\TextColumn::make('keywords_limit')
                    ->label('Keywords')
                    ->formatStateUsing(fn (?int $state) => $state === null ? 'Unlimited' : $state),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('sort_order')
            ->defaultSort('sort_order');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit'   => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
