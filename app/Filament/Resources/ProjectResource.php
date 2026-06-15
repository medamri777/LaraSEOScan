<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProjectResource\Pages;
use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProjectResource extends Resource
{
    protected static ?string $model           = Project::class;
    protected static ?string $navigationIcon  = 'heroicon-o-folder';
    protected static ?string $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Projects';
    protected static ?int    $navigationSort  = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Project Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('url')
                    ->label('Website URL')
                    ->url()
                    ->required()
                    ->maxLength(2048),
                Forms\Components\Select::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->rows(3)
                    ->maxLength(1000)
                    ->columnSpanFull(),
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
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->searchable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Workspace')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('scans_count')
                    ->label('Scans')
                    ->counts('scans')
                    ->sortable(),
                Tables\Columns\TextColumn::make('keywords_count')
                    ->label('Keywords')
                    ->counts('keywords')
                    ->sortable(),
                Tables\Columns\TextColumn::make('competitors_count')
                    ->label('Competitors')
                    ->counts('competitors')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
            'index'  => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit'   => Pages\EditProject::route('/{record}/edit'),
            'view'   => Pages\ViewProject::route('/{record}'),
        ];
    }
}
