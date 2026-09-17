<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasskeyUser;
use Laravel\Fortify\PasskeyAuthenticatable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property Carbon|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'two_factor_secret', 'two_factor_recovery_codes', 'remember_token'])]
class User extends Authenticatable implements FilamentUser, PasskeyUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable, PasskeyAuthenticatable, TwoFactorAuthenticatable;

    /**
     * Workshop roles (single source of truth — must match the roles table).
     */
    public const ROLE_MANAGER = 'مدير النظام';

    public const ROLE_STAFF = 'موظف';

    public const ROLE_TECHNICIAN = 'فني';

    public function isManager(): bool
    {
        return $this->hasRole(self::ROLE_MANAGER);
    }

    public function isTechnician(): bool
    {
        return $this->hasRole(self::ROLE_TECHNICIAN);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Only staff members may enter the Filament panel. Plain registered
     * users (no role) are blocked — they must not see anything.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole([self::ROLE_MANAGER, self::ROLE_STAFF, self::ROLE_TECHNICIAN]);
    }

    /**
     * Repair orders assigned to the user as technician.
     *
     * @return HasMany<RepairOrder>
     */
    public function repairOrders(): HasMany
    {
        return $this->hasMany(RepairOrder::class, 'technician_id');
    }

    /**
     * Scope to users holding the technician role.
     *
     * Uses whereHas (same as TechnicianResource) so missing roles
     * simply yield zero rows instead of throwing.
     */
    public function scopeTechnicians(Builder $query): void
    {
        $query->whereHas('roles', fn (Builder $roles): Builder => $roles->where('name', self::ROLE_TECHNICIAN));
    }

    /**
     * Append live workload counters (active + ready-for-delivery orders).
     */
    public function scopeWithWorkloadCounts(Builder $query): void
    {
        $active = collect(RepairOrderStatus::all())
            ->reject(fn (string $status): bool => RepairOrderStatus::isTerminal($status))
            ->values()
            ->all();

        $query->withCount([
            'repairOrders as active_orders_count' => fn (Builder $orders): Builder => $orders->whereIn('status', $active),
            'repairOrders as ready_orders_count' => fn (Builder $orders): Builder => $orders->where('status', RepairOrderStatus::READY_FOR_DELIVERY),
        ]);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        $initials = Str::initials($this->name, true);

        return Str::length($initials) > 1
            ? Str::substr($initials, 0, 1).Str::substr($initials, -1)
            : $initials;
    }
}
