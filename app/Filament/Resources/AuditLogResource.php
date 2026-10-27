<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';
    protected static ?string $navigationLabel = 'Audit Trail';
    protected static ?string $modelLabel = 'audit entry';
    protected static ?string $pluralModelLabel = 'audit trail';

    protected static ?int $navigationSort = 99;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    // ── Access: read-only, and only for permitted viewers ────────────────────

    /** Role "admin", or an email in AUDIT_VIEWER_EMAILS. If that list is empty, any panel user. */
    public static function userMayView(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        $allowed = config('audit.viewer_emails', []);

        if ($allowed === []) {
            return true;
        }

        return data_get($user, 'role') === 'admin'
            || in_array(strtolower((string) data_get($user, 'email')), $allowed, true);
    }

    public static function canViewAny(): bool { return static::userMayView(); }
    public static function canView(Model $record): bool { return static::userMayView(); }
    public static function canCreate(): bool { return false; }
    public static function canEdit(Model $record): bool { return false; }
    public static function canDelete(Model $record): bool { return false; }
    public static function canDeleteAny(): bool { return false; }

    // ── Table ────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->striped()
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                ->label('When')
                ->dateTime('d M Y,  H:i:s')
                ->sortable(),

                Tables\Columns\TextColumn::make('actor_name')
                ->label('Who')
                ->placeholder('System/Guest')
                ->description(fn(AuditLog $record) => $record->actor_role)
                ->searchable(),

                Tables\Columns\TextColumn::make('event')
                ->badge()
                ->color(fn(string $state): string => match ($state){
                    'created', 'payment_verified' => 'success',
                    'updated' => 'info',
                    'deleted' => 'danger',
                    'login_failed' => 'warning',
                    default => 'gray',
                })
                ->sortable(),

                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Record type')
                    ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),

                Tables\Columns\TextColumn::make('subject_label')
                    ->label('Record')
                    ->placeholder('—')
                    ->searchable(),

                Tables\Columns\TextColumn::make('changes')
                    ->label('Changes')
                    ->state(fn (AuditLog $record) => $record->changeSummary())
                    ->wrap()
                    ->limit(120),

                Tables\Columns\TextColumn::make('reason')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('source')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->multiple()
                    ->options(fn () => AuditLog::query()->distinct()->orderBy('event')->pluck('event', 'event')->all()),

                Tables\Filters\SelectFilter::make('subject_type')
                    ->label('Record type')
                    ->options(fn () => AuditLog::query()
                        ->whereNotNull('subject_type')->distinct()->pluck('subject_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->all()),

                Tables\Filters\SelectFilter::make('actor_id')
                    ->label('User')
                    ->options(fn () => AuditLog::query()
                        ->whereNotNull('actor_id')->distinct()->pluck('actor_name', 'actor_id')->all()),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $q, $date) => $q->whereDate('created_at', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([]);
    }

    // ── Detail page ──────────────────────────────────────────────────────────

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Summary')
                ->columns(3)
                ->schema([
                    TextEntry::make('created_at')->label('When')->dateTime('d M Y, H:i:s'),
                    TextEntry::make('actor_name')->label('Who')->placeholder('System / Guest'),
                    TextEntry::make('event')->badge(),
                    TextEntry::make('subject_type')
                        ->label('Record type')
                        ->formatStateUsing(fn (?string $state) => $state ? class_basename($state) : '—'),
                    TextEntry::make('subject_label')->label('Record')->placeholder('—'),
                    TextEntry::make('reason')->placeholder('—'),
                    TextEntry::make('description')->placeholder('—')->columnSpanFull(),
                ]),

            Section::make('What changed')
                ->schema([
                    ViewEntry::make('changes')
                        ->hiddenLabel()
                        ->state(fn (AuditLog $record) => [
                            'old'        => $record->old_values ?? [],
                            'new'        => $record->new_values ?? [],
                            'properties' => $record->properties ?? [],
                        ])
                        ->view('filament.audit.changes')
                        ->columnSpanFull(),
                ]),

            Section::make('Request')
                ->columns(3)
                ->collapsed()
                ->schema([
                    TextEntry::make('ip_address')->label('IP address')->placeholder('—'),
                    TextEntry::make('source')->placeholder('—'),
                    TextEntry::make('user_agent')->label('Browser')->placeholder('—')->columnSpanFull(),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view'  => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
