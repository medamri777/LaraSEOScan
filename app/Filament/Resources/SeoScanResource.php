<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeoScanResource\Pages;
use App\Models\SeoScan;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SeoScanResource extends Resource
{
    protected static ?string $model           = SeoScan::class;
    protected static ?string $navigationIcon  = 'heroicon-o-magnifying-glass';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'SEO Scans';
    protected static ?int    $navigationSort  = 4;

    public static function form(Form $form): Form
    {
        // Read-only resource — no create/edit form needed
        return $form->schema([]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Scan Info')->schema([
                Infolists\Components\TextEntry::make('url'),
                Infolists\Components\TextEntry::make('uuid')->label('UUID'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        default     => 'gray',
                    }),
                Infolists\Components\TextEntry::make('user.name')->label('User'),
                Infolists\Components\TextEntry::make('project.name')->label('Project'),
                Infolists\Components\TextEntry::make('time_elapsed')
                    ->label('Time (s)')
                    ->formatStateUsing(fn (?float $state) => $state ? round($state, 2) . 's' : '---'),
                Infolists\Components\TextEntry::make('created_at')->dateTime('d/m/Y H:i'),
            ])->columns(3),

            Infolists\Components\Section::make('Overall Scores')->schema([
                Infolists\Components\TextEntry::make('score_total')
                    ->label('Total')
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state >= 80 => 'success',
                        $state >= 50 => 'warning',
                        default      => 'danger',
                    }),
                Infolists\Components\TextEntry::make('score_technical')->label('Technical'),
                Infolists\Components\TextEntry::make('score_on_page')->label('On-Page'),
                Infolists\Components\TextEntry::make('score_local')->label('Local'),
                Infolists\Components\TextEntry::make('score_mobile')->label('Mobile'),
                Infolists\Components\TextEntry::make('score_speed')->label('Speed'),
            ])->columns(6),

            Infolists\Components\Section::make('PageSpeed Insights')->schema([
                Infolists\Components\TextEntry::make('pagespeed_performance')->label('Performance'),
                Infolists\Components\TextEntry::make('pagespeed_seo')->label('SEO'),
                Infolists\Components\TextEntry::make('pagespeed_accessibility')->label('Accessibility'),
                Infolists\Components\TextEntry::make('pagespeed_best_practices')->label('Best Practices'),
            ])->columns(4),

            Infolists\Components\Section::make('Crawl Stats')->schema([
                Infolists\Components\TextEntry::make('total_urls_found')->label('URLs Found'),
                Infolists\Components\IconEntry::make('has_robots_txt')->label('robots.txt')->boolean(),
                Infolists\Components\IconEntry::make('has_sitemap_xml')->label('sitemap.xml')->boolean(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->width(50),
                Tables\Columns\TextColumn::make('url')
                    ->limit(50)
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'failed'    => 'danger',
                        default     => 'gray',
                    }),
                Tables\Columns\TextColumn::make('score_total')
                    ->label('Score')
                    ->sortable()
                    ->badge()
                    ->color(fn (?int $state): string => match (true) {
                        $state === null => 'gray',
                        $state >= 80    => 'success',
                        $state >= 50    => 'warning',
                        default         => 'danger',
                    }),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('time_elapsed')
                    ->label('Time')
                    ->formatStateUsing(fn (?float $state) => $state ? round($state, 1) . 's' : '---')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'failed'    => 'Failed',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')->label('From'),
                        Forms\Components\DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['created_from'], fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['created_until'], fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    }),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeoScans::route('/'),
            'view'  => Pages\ViewSeoScan::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
