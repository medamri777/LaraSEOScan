<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model           = User::class;
    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Users & Access';
    protected static ?string $navigationLabel = 'All Users';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('User Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->maxLength(30),
                Forms\Components\TextInput::make('company')
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Access & Workspace')->schema([
                Forms\Components\Select::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload()
                    ->nullable(),
                Forms\Components\Select::make('tenant_role')
                    ->label('Workspace Role')
                    ->options([
                        'owner'  => 'Owner',
                        'member' => 'Member',
                    ])
                    ->nullable(),
                Forms\Components\Toggle::make('is_admin')
                    ->label('Platform Admin')
                    ->helperText('Grants full access to this admin panel.'),
            ])->columns(2),

            Forms\Components\Section::make('Password')->schema([
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->nullable()
                    ->helperText('Leave blank to keep the current password.'),
            ]),
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
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label('Workspace')
                    ->searchable()
                    ->placeholder('—'),
                Tables\Columns\TextColumn::make('tenant_role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'owner'  => 'warning',
                        'member' => 'gray',
                        default  => 'gray',
                    })
                    ->placeholder('—'),
                Tables\Columns\IconColumn::make('is_admin')
                    ->label('Admin')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('is_admin')
                    ->label('Admins only')
                    ->query(fn ($query) => $query->where('is_admin', true)),
                Tables\Filters\SelectFilter::make('tenant_id')
                    ->label('Workspace')
                    ->relationship('tenant', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'edit'  => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
