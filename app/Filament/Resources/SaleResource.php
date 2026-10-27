<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SaleResource\Pages;
use App\Models\Sale;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\Summarizers\Average;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Sales & Profits';
    protected static ?int    $navigationSort = 2;

    // Sales are system-generated — no create or edit
    public static function canCreate(): bool { return false; }

    public static function getNavigationBadge(): ?string
    {
        return (string) Sale::whereMonth('created_at', now()->month)->count();
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // read-only resource
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('order_reference')
                    ->label('Order Ref')
                    ->searchable()
                    ->copyable()
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                TextColumn::make('customer_name')
                    ->label('Customer')
                    ->searchable(),
                TextColumn::make('product.name')
                    ->label('Product')
                    ->placeholder('—'),
                TextColumn::make('variation_label')
                    ->label('Variation')
                    ->badge()
                    ->color('info')
                    ->placeholder('—'),
                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),
                TextColumn::make('selling_price')
                    ->label('Selling Price')
                    ->money('KES')
                    ->sortable(),
                TextColumn::make('buying_price')
                    ->label('Buying Price')
                    ->money('KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('shipping_cost')
                    ->label('Shipping')
                    ->money('KES')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_revenue')
                    ->label('Revenue')
                    ->money('KES')
                    ->sortable()
                    ->summarize([
                        Sum::make()->label('Total Revenue')->money('KES'),
                    ]),
                TextColumn::make('total_cost')
                    ->label('Cost')
                    ->money('KES')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->summarize([
                        Sum::make()->label('Total Cost')->money('KES'),
                    ]),
                TextColumn::make('profit')
                    ->label('Profit / Loss')
                    ->money('KES')
                    ->sortable()
                    ->color(fn($state) => $state >= 0 ? 'success' : 'danger')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->summarize([
                        Sum::make()->label('Net Profit')->money('KES'),
                    ]),
                TextColumn::make('shipping_method')
                    ->label('Shipping Zone')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                // ── Quick period filters ──────────────────────────────────────
                Filter::make('today')
                    ->label('Today')
                    ->query(fn(Builder $query) => $query->whereDate('created_at', today()))
                    ->toggle(),

                Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn(Builder $query) => $query->whereBetween('created_at', [
                        now()->startOfWeek(), now()->endOfWeek()
                    ]))
                    ->toggle(),

                Filter::make('this_month')
                    ->label('This Month')
                    ->query(fn(Builder $query) => $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year))
                    ->toggle(),

                // ── Custom date range — two calendar pickers ──────────────────
                Filter::make('date_range')
                    ->label('Custom Period')
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(false)
                            ->displayFormat('d M Y'),
                        DatePicker::make('until')
                            ->label('Until')
                            ->native(false)
                            ->displayFormat('d M Y'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'],  fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'])  $indicators[] = 'From: ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                        if ($data['until']) $indicators[] = 'Until: ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                        return $indicators;
                    }),
            ])
            ->filtersLayout(Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSales::route('/'),
        ];
    }
}
