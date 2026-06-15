<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TenantResource\Pages;
use App\Filament\Resources\TenantResource\RelationManagers\PlanChangeLogsRelationManager;
use App\Models\Plan;
use App\Models\PlanChangeLog;
use App\Models\Tenant;
use App\Support\PlanLimits;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class TenantResource extends Resource
{
    protected static ?string $model           = Tenant::class;
    protected static ?string $navigationIcon  = 'heroicon-o-building-office';
    protected static ?string $navigationGroup = 'Platform';
    protected static ?string $navigationLabel = 'Workspaces';
    protected static ?int    $navigationSort  = 1;

    /**
     * Build plan options from DB, falling back to basic list.
     */
    private static function planOptions(): array
    {
        try {
            $plans = Plan::active()->get();
            if ($plans->isNotEmpty()) {
                return $plans->mapWithKeys(fn (Plan $p) => [
                    $p->slug => $p->display_name,
                ])->toArray();
            }
        } catch (\Throwable) {
            // Table may not exist yet
        }

        return [
            'free'     => 'Free',
            'pro'      => 'Pro',
            'guru'     => 'Guru',
            'business' => 'Business',
            'agency'   => 'Agency',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Workspace Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('slug')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\Select::make('plan')
                    ->options(static::planOptions())
                    ->required()
                    ->default('free'),
                Forms\Components\TextInput::make('scan_limit_per_day')
                    ->label('Daily Scan Limit')
                    ->numeric()
                    ->default(10)
                    ->minValue(1)
                    ->maxValue(10000),
            ])->columns(2),

            Forms\Components\Section::make('Agency Branding (White-label)')->schema([
                Forms\Components\TextInput::make('agency_name')
                    ->label('Agency Name')
                    ->maxLength(255)
                    ->placeholder('Acme Digital Agency'),
                Forms\Components\TextInput::make('agency_website')
                    ->label('Agency Website')
                    ->url()
                    ->maxLength(2048)
                    ->placeholder('https://acme.ma'),
                Forms\Components\ColorPicker::make('primary_color')
                    ->label('Brand Colour')
                    ->default('#3B82F6'),
                Forms\Components\Placeholder::make('logo_info')
                    ->label('Logo Status')
                    ->content(fn (?Tenant $record): string => $record?->logo_path
                        ? "Logo uploaded: {$record->logo_path}"
                        : 'No logo uploaded yet. Users upload via workspace settings.'
                    ),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->width(60),
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('plan')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'free'     => 'gray',
                        'pro'      => 'warning',
                        'guru'     => 'info',
                        'business' => 'danger',
                        'agency'   => 'success',
                        default    => 'gray',
                    }),
                Tables\Columns\TextColumn::make('scan_limit_per_day')
                    ->label('Scans/Day')
                    ->formatStateUsing(fn (?int $state) => $state ?? '---')
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('projects_count')
                    ->label('Projects')
                    ->counts('projects')
                    ->sortable(),
                Tables\Columns\IconColumn::make('logo_path')
                    ->label('Logo')
                    ->boolean()
                    ->trueIcon('heroicon-o-photo')
                    ->falseIcon('heroicon-o-minus'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->options(static::planOptions()),
            ])
            ->actions([
                Action::make('changePlan')
                    ->label('Change Plan')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->form([
                        Forms\Components\Section::make('Plan Override')->schema([
                            Forms\Components\Placeholder::make('current_plan_info')
                                ->label('Current Plan')
                                ->content(fn (Tenant $record): string =>
                                    ucfirst($record->plan) . ' --- ' .
                                    ($record->scan_limit_per_day
                                        ? "{$record->scan_limit_per_day} scans/day"
                                        : 'Default limit'
                                    )
                                ),

                            Forms\Components\Select::make('new_plan')
                                ->label('New Plan')
                                ->options(static::planOptions())
                                ->required()
                                ->live()
                                ->afterStateUpdated(function ($state, Forms\Set $set) {
                                    $set('new_scan_limit', PlanLimits::scanLimitPerDay($state));
                                }),

                            Forms\Components\TextInput::make('new_scan_limit')
                                ->label('Custom Daily Scan Limit')
                                ->helperText('Leave blank to use plan default. Set a custom value to override.')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(100000)
                                ->nullable(),

                            Forms\Components\Textarea::make('note')
                                ->label('Reason / Note')
                                ->helperText('Explain why this change was made --- e.g. "Client paid via bank transfer for Pro plan, ref: VIR-2026-001"')
                                ->required()
                                ->rows(3)
                                ->maxLength(1000),
                        ]),
                    ])
                    ->action(function (Tenant $record, array $data): void {
                        $oldPlan  = $record->plan;
                        $oldLimit = $record->scan_limit_per_day;
                        $newPlan  = $data['new_plan'];
                        $newLimit = isset($data['new_scan_limit']) && $data['new_scan_limit'] !== ''
                            ? (int) $data['new_scan_limit']
                            : PlanLimits::scanLimitPerDay($newPlan);

                        $record->update([
                            'plan'              => $newPlan,
                            'scan_limit_per_day' => $newLimit,
                        ]);

                        PlanChangeLog::create([
                            'tenant_id'      => $record->id,
                            'admin_id'       => Auth::id(),
                            'old_plan'       => $oldPlan,
                            'new_plan'       => $newPlan,
                            'old_scan_limit' => $oldLimit,
                            'new_scan_limit' => $newLimit,
                            'source'         => 'admin',
                            'note'           => $data['note'],
                        ]);

                        Notification::make()
                            ->title('Plan updated')
                            ->body("Workspace \"{$record->name}\" upgraded from " . ucfirst($oldPlan) . ' to ' . ucfirst($newPlan) . '.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Override Workspace Plan')
                    ->modalDescription('This change takes effect immediately. A log entry will be created for audit purposes.')
                    ->modalSubmitActionLabel('Apply Plan Change')
                    ->modalWidth('lg'),

                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            PlanChangeLogsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit'   => Pages\EditTenant::route('/{record}/edit'),
            'view'   => Pages\ViewTenant::route('/{record}'),
        ];
    }
}
