<?php

namespace App\Filament\Resources;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Filament\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Components\ToggleButtons;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action as NotificationAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Set;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\Section;
use App\Models\ShippingZone;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    public static function getNavigationBadge(): string
    {
        return static::getModel()::count();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // STEP 1: Customer Information
                    Step::make('Order Details')
                        ->description('Review customer and shipping info')
                        ->icon('heroicon-m-user')
                        ->schema([
                            Section::make('Customer Information')
                                ->schema([
                                    Forms\Components\TextInput::make('first_name')->required(),
                                    Forms\Components\TextInput::make('last_name')->required(),
                                    Forms\Components\TextInput::make('phone')->tel()->required(),
                                    Forms\Components\TextInput::make('email')->email()->required(),
                                    Forms\Components\TextInput::make('shipping_address')->required(),
                                    Forms\Components\TextInput::make('city')->required(),
                                ])->columns(2),
                        ]),

                    // STEP 2: Order Items/Details
                    Step::make('Order Items')
                        ->description('Select handbags and payment')
                        ->icon('heroicon-m-shopping-cart')
                        ->schema([
                            Section::make('Order Details')
                                ->schema([
                                    Forms\Components\Select::make('product_id')
                                        ->label('Handbag')
                                        ->relationship('product', 'name')
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                    Forms\Components\TextInput::make('variation_label')
                                        ->label('Variation / Color')
                                        ->placeholder('No variation selected'),
                                    Forms\Components\TextInput::make('quantity')
                                        ->label('Quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->minValue(1)
                                        ->required(),
                                    Forms\Components\TextInput::make('order_reference')
                                        ->label('Order Reference')
                                        ->disabled()
                                        ->placeholder('Auto-generated'),
                                    Forms\Components\TextInput::make('total_amount')
                                        ->required()
                                        ->numeric()
                                        ->prefix('KES'),

                                    Forms\Components\Select::make('shipping_method_name')
                                        ->label('Shipping Zone')
                                        ->options(ShippingZone::all()->pluck('name', 'name'))
                                        ->searchable()
                                        ->reactive()
                                        ->required()
                                        ->afterStateUpdated(function ($state, Set $set) {
                                            $zone = ShippingZone::where('name', $state)->first();
                                            $set('shipping_cost', $zone ? $zone->price : 0);
                                        }),

                                    TextInput::make('shipping_cost')
                                        ->numeric()
                                        ->prefix('KES')
                                        ->placeholder('KES 0.00')
                                        ->readonly(),

                                    Forms\Components\ToggleButtons::make('status')
                                        ->options([
                                            'new' => 'New',
                                            'processing' => 'Processing',
                                            'shipped' => 'Shipped',
                                            'delivered' => 'Delivered',
                                            'cancelled' => 'Cancelled',
                                        ])
                                        ->icons([
                                            'new' => 'heroicon-m-sparkles',
                                            'processing' => 'heroicon-m-arrow-path',
                                            'shipped' => 'heroicon-m-truck',
                                            'delivered' => 'heroicon-m-check-badge',
                                            'cancelled' => 'heroicon-m-x-circle',
                                        ])
                                        ->colors([
                                            'new' => 'primary',
                                            'processing' => 'gray',
                                            'shipped' => 'warning',
                                            'delivered' => 'success',
                                            'cancelled' => 'danger',
                                        ])
                                        ->inline()
                                        ->required(),

                                    TextInput::make('pickup_agent_details')
                                        ->label('Pick Up Mtaani / Agent Details')
                                        ->placeholder('Enter Pick Up Mtaani / Agent Details if applicable')
                                        ->columnSpanFull(),

                                    Forms\Components\TextInput::make('payment_method')
                                        ->default('M-Pesa')
                                        ->required(),

                                    // ── NEW: surfaces the customer-submitted M-Pesa receipt details ──
                                    Forms\Components\TextInput::make('mpesa_transaction_id')
                                        ->label('M-Pesa Transaction Code')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('Not yet submitted by customer')
                                        ->suffixIcon('heroicon-m-clipboard-document'),

                                    Forms\Components\TextInput::make('payment_phone_number')
                                        ->label('M-Pesa Number Used')
                                        ->disabled()
                                        ->dehydrated(false)
                                        ->placeholder('Not yet submitted by customer'),

                                    Forms\Components\ToggleButtons::make('payment_status')
                                        ->options([
                                            'pending' => 'Pending',
                                            'awaiting_verification' => 'Awaiting Verification',
                                            'completed' => 'Completed',
                                            'failed' => 'Failed',
                                        ])
                                        ->colors([
                                            'pending' => 'gray',
                                            'awaiting_verification' => 'warning',
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                        ])
                                        ->inline()
                                        ->columnSpanFull(),
                                ])->columns(2),
                        ]),
                ])
                    ->columnSpanFull()
                    ->skippable()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order #')
                    ->prefix('KRC')
                    ->searchable(),
                TextColumn::make('order_reference')
                    ->label('Order Reference')
                    ->prefix('KRC')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Reference copied')
                    ->placeholder('—')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold),
                TextColumn::make('first_name')
                    ->label('Customer')
                    ->searchable()
                    ->formatStateUsing(fn($record) => "{$record->first_name} {$record->last_name}"),

                // ── NEW: M-Pesa code visible directly in the table for fast verification ──
                TextColumn::make('mpesa_transaction_id')
                    ->label('M-Pesa Code')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Code copied')
                    ->placeholder('— not submitted —')
                    ->badge()
                    ->color(fn($state) => $state ? 'success' : 'gray'),

                TextColumn::make('payment_phone_number')
                    ->label('Paid From')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->badge()
                    ->color(fn($state) => match ($state) {
                        'pending' => 'gray',
                        'awaiting_verification' => 'warning',
                        'completed' => 'success',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn($state) => match ($state) {
                        'pending' => 'Pending',
                        'awaiting_verification' => 'Awaiting Verification',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                        default => ucfirst($state),
                    }),
                TextColumn::make('shipping_method_name')
                    ->label('Shipping Zone')
                    ->searchable()
                    ->badge(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn(?string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'shipped' => 'success',
                        'delivered' => 'primary',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn(?string $state): ?string => match ($state) {
                        'new' => 'heroicon-m-sparkles',
                        'shipped' => 'heroicon-m-truck',
                        'delivered' => 'heroicon-m-check-circle',
                        'cancelled' => 'heroicon-m-x-circle',
                        default => null,
                    }),
                TextColumn::make('product.name')
                    ->label('Product Name')
                    ->default('No Product Assigned')
                    ->placeholder('No Product Assigned')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('variation_label')
                    ->label('Variation')
                    ->badge()
                    ->placeholder('—')
                    ->color('info'),
                TextColumn::make('total_amount')
                    ->label('Total price')
                    ->money('KES')
                    ->sortable()
                    ->summarize(Sum::make()->label('Total')),
                TextColumn::make('created_at')
                    ->label('Order Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'new' => 'New',
                        'shipped' => 'Shipped',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('payment_status')
                    ->options([
                        'pending' => 'Pending',
                        'awaiting_verification' => 'Awaiting Verification',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                // ── Verification: updates the order, then offers a one-click
                //     WhatsApp message (pre-filled) for the admin to send manually
                //     via the shop's own WhatsApp account. No API key, no third-party
                //     service — this is the wa.me click-to-chat protocol.
                Tables\Actions\Action::make('verify_payment')
                    ->label('Verify Payment')
                    ->icon('heroicon-m-check-badge')
                    ->color('success')
                    ->visible(fn(Order $record) => $record->payment_status === 'awaiting_verification')
                    ->requiresConfirmation()
                    ->modalHeading('Confirm M-Pesa Payment')
                    ->modalDescription(fn(Order $record) => "Confirm you have checked the M-Pesa statement for code {$record->mpesa_transaction_id} matching KES " . number_format($record->total_amount) . ".")
                    ->action(function (Order $record) {
                        $record->update([
                            'payment_status' => 'completed',
                            'status' => 'processing',
                        ]);

                        Notification::make()
                            ->title('Payment verified')
                            ->body("Order {$record->order_reference} marked as paid and moved to Processing. Notify {$record->first_name} on WhatsApp below.")
                            ->success()
                            ->persistent()
                            ->actions([
                                NotificationAction::make('send_whatsapp')
                                    ->label('Send WhatsApp Update')
                                    ->icon('heroicon-m-chat-bubble-left-right')
                                    ->button()
                                    ->color('success')
                                    ->url(static::buildWhatsAppLink($record, 'verified'))
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    }),

                // ── Optional manual nudge: lets staff re-send the shipped update
                //     on WhatsApp at any time, independent of the automatic email ──
                Tables\Actions\Action::make('whatsapp_shipped')
                    ->label('WhatsApp Shipping Update')
                    ->icon('heroicon-m-chat-bubble-left-right')
                    ->color('gray')
                    ->visible(fn(Order $record) => in_array($record->status, ['shipped', 'delivered']))
                    ->url(fn(Order $record) => static::buildWhatsAppLink($record, 'shipped'))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('print_invoice')
                    ->label('Print Invoice')
                    ->icon('heroicon-m-printer')
                    ->color('info')
                    ->action(function (Order $record) {
                        return response()->streamDownload(function () use ($record) {
                            echo Pdf::loadView('invoice', ['record' => $record])->output();
                        }, "Invoice-KRC-{$record->id}.pdf");
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Builds a wa.me click-to-chat link with a pre-filled message.
     * The admin still has to press send in WhatsApp themselves — this is
     * intentional: it requires zero API keys, zero third-party services,
     * and keeps a human in the loop before anything reaches the customer.
     */
    protected static function buildWhatsAppLink(Order $record, string $type = 'verified'): string
    {
        // Normalize Kenyan numbers to international format with no symbols
        $phone = preg_replace('/\D/', '', (string) $record->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '254' . substr($phone, 1);
        } elseif (str_starts_with($phone, '7') || str_starts_with($phone, '1')) {
            $phone = '254' . $phone;
        }

        $amount = 'KES ' . number_format($record->total_amount);

        $message = match ($type) {
            'verified' => "Hi {$record->first_name}, this is KituRare Collections. We've received and verified your M-Pesa payment of {$amount} for order {$record->order_reference}. Your order is now being prepared for shipping to {$record->city} {$record->shipping_method_name}. We'll message you again once it's on its way. Thank you for shopping with us!",
            'shipped'  => "Hi {$record->first_name}, your KituRare order {$record->order_reference} has shipped! It's on its way to {$record->city} via {$record->shipping_method_name}. We'll let you know once it's delivered.",
            default    => "Hi {$record->first_name}, this is KituRare Collections regarding your order {$record->order_reference}.",
        };

        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
