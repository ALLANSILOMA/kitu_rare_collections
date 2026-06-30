<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use App\Services\ProductCsvImport;
use App\Services\ProductImporter;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;
    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    public static function getNavigationBadge(): string
    {
        return static::getModel()::count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 10 ? 'success' : 'warning';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([

            Section::make('Product Details')->schema([
                Forms\Components\TextInput::make('name')->required()->columnSpanFull(),
                Forms\Components\RichEditor::make('description')
                    ->toolbarButtons(['bold','italic','underline','strike','link','h2','h3','bulletList','orderedList','undo','redo'])
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('price')->numeric()->prefix('KES')
                    ->helperText('Single price shared across all variations.'),
                Forms\Components\TextInput::make('stock')->required()->numeric()->default(0)
                    ->helperText('Total stock shared across all variations.'),
            ])->columns(2),

            Section::make('Product Images')
                ->description('Main gallery images. You can also add per-variation images in the Variations section below.')
                ->schema([
                    FileUpload::make('images')->label('')->image()->multiple()->maxFiles(10)
                        ->disk('public')->directory('product')->visibility('public')
                        ->imageEditor()->columnSpanFull(),
                ]),

            Section::make('Variations')
                ->description('Define Color, Size, and/or Material options. All options share the same price and stock.')
                ->schema([
                    Repeater::make('variations')->label('')
                        ->schema([
                            Select::make('type')->label('Variation Type')
                                ->options(['Color'=>'Color','Size'=>'Size','Material'=>'Material'])
                                ->required()->native(false)->columnSpanFull(),
                            Repeater::make('options')->label('Options')
                                ->schema([
                                    TextInput::make('label')->label('Option Name')
                                        ->placeholder('e.g. Black, Large, Leather')->required(),
                                    Forms\Components\ColorPicker::make('hex')
                                        ->label('Swatch Color')->default('#000000'),
                                    FileUpload::make('image')->label('Option Image (optional)')
                                        ->image()->disk('public')->directory('product/variations')
                                        ->visibility('public')->imageEditor()
                                        ->helperText('Leave blank to use main product images.'),
                                ])
                                ->columns(2)->addActionLabel('+ Add Option')
                                ->defaultItems(1)->reorderable()->columnSpanFull(),
                        ])
                        ->addActionLabel('+ Add Variation Type')->columnSpanFull()
                        ->collapsible()->defaultItems(0)->reorderable()
                        ->itemLabel(fn (array $state): ?string => $state['type'] ?? null),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->sortable()->searchable()->weight(FontWeight::Medium),

                Tables\Columns\TextColumn::make('description')
                    ->html()->wrap()->lineClamp(2)->searchable(),

                Tables\Columns\ImageColumn::make('images')
                    ->label('Product Preview')->disk('public')
                    ->circular()->stacked()->limit(3)->visibility('public'),

                Tables\Columns\TextColumn::make('variations')
                    ->label('Variations')
                    ->getStateUsing(function (\Illuminate\Database\Eloquent\Model $record): string {
                        $variations = $record->getAttribute('variations');
                        if (empty($variations)) return '—';
                        $data = is_string($variations) ? json_decode($variations, true) : $variations;
                        if (empty($data) || !is_array($data)) return '—';
                        return collect($data)->map(fn ($v) => $v['type'] ?? null)->filter()->join(' · ');
                    })
                    ->badge()->color('gray'),

                Tables\Columns\TextColumn::make('price')
                    ->money('KES')->sortable()->alignment('right'),

                Tables\Columns\TextColumn::make('stock')->label('Stock')
                    ->numeric()->sortable()->badge()
                    ->color(fn (int $state): string => match (true) {
                        $state <= 0 => 'danger',
                        $state <= 5 => 'warning',
                        default     => 'success',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Filter::make('price')
                    ->form([
                        TextInput::make('price_from')->numeric()->label('Min Price'),
                        TextInput::make('price_to')->numeric()->label('Max Price'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['price_from'], fn ($q, $p) => $q->where('price', '>=', $p))
                        ->when($data['price_to'],   fn ($q, $p) => $q->where('price', '<=', $p))),

                Filter::make('stock')->toggle()->label('In Stock Only')
                    ->query(fn (Builder $query) => $query->where('stock', '>', 0)),

                Filter::make('has_variations')->toggle()->label('Has Variations')
                    ->query(fn (Builder $query) => $query->whereNotNull('variations')),
            ])

            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('restock')
                    ->label('Restock')->icon('heroicon-o-plus-circle')->color('success')
                    ->form([
                        TextInput::make('amount')->label('Units to add')
                            ->numeric()->required()->minValue(1),
                    ])
                    ->action(function (\App\Models\Product $record, array $data): void {
                        $record->increment('stock', (int) $data['amount']);
                        \Filament\Notifications\Notification::make()
                            ->title('Stock updated')
                            ->body("{$record->name} restocked by {$data['amount']} unit(s).")
                            ->success()->send();
                    })
                    ->visible(fn (\App\Models\Product $record): bool =>
                        $record->getAttribute('stock') <= 5),

                Tables\Actions\DeleteAction::make(),
            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getWidgets(): array
    {
        return [ProductResource\Widgets\ProductStats::class];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->check();
    }
}
