<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KeywordResource\Pages;
use App\Models\Keyword;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class KeywordResource extends Resource
{
    protected static ?string $model           = Keyword::class;
    protected static ?string $navigationIcon  = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Keywords';
    protected static ?int    $navigationSort  = 5;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Keyword Details')->schema([
                Forms\Components\TextInput::make('keyword')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Select::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\TextInput::make('location_code')
                    ->maxLength(20)
                    ->placeholder('e.g. 250 (France)'),
                Forms\Components\TextInput::make('language_code')
                    ->maxLength(10)
                    ->placeholder('e.g. en'),
                Forms\Components\Select::make('device')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile'  => 'Mobile',
                        'tablet'  => 'Tablet',
                    ])
                    ->default('desktop'),
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
                    ->width(50),
                Tables\Columns\TextColumn::make('keyword')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Project')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('device')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'desktop' => 'primary',
                        'mobile'  => 'warning',
                        'tablet'  => 'info',
                        default   => 'gray',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('last_checked_at')
                    ->label('Last Checked')
                    ->dateTime('d/m/Y')
                    ->sortable()
                    ->placeholder('Never'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name'),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Project')
                    ->relationship('project', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\SelectFilter::make('device')
                    ->options([
                        'desktop' => 'Desktop',
                        'mobile'  => 'Mobile',
                        'tablet'  => 'Tablet',
                    ]),
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
            ->defaultSort('id', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKeywords::route('/'),
            'edit'  => Pages\EditKeyword::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
