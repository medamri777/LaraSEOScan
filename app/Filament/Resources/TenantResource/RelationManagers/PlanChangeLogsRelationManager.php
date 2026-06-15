<?php

namespace App\Filament\Resources\TenantResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PlanChangeLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'planChangeLogs';
    protected static ?string $title       = 'Plan Change History';
    protected static ?string $icon        = 'heroicon-o-clipboard-document-list';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('old_plan')
                    ->label('From')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state ? ucfirst($state) : '—')
                    ->color(fn (?string $state): string => match ($state) {
                        'pro'    => 'warning',
                        'agency' => 'success',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('new_plan')
                    ->label('To')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => ucfirst($state))
                    ->color(fn (string $state): string => match ($state) {
                        'pro'    => 'warning',
                        'agency' => 'success',
                        default  => 'gray',
                    }),

                Tables\Columns\TextColumn::make('old_scan_limit')
                    ->label('Old Scan Limit')
                    ->formatStateUsing(fn (?int $state) => $state ?? '—'),

                Tables\Columns\TextColumn::make('new_scan_limit')
                    ->label('New Scan Limit')
                    ->formatStateUsing(fn (?int $state) => $state ?? 'Plan default'),

                Tables\Columns\TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'stripe' => 'info',
                        'system' => 'gray',
                        default  => 'primary',
                    }),

                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Changed By')
                    ->default('System'),

                Tables\Columns\TextColumn::make('note')
                    ->label('Note')
                    ->limit(60)
                    ->tooltip(fn (?string $state) => $state)
                    ->default('—'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25])
            ->headerActions([])
            ->actions([])
            ->bulkActions([]);
    }
}
