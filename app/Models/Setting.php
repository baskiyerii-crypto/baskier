<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public static function get(string $key, $default = null)
    {
        $settings = Cache::remember('settings', 3600, function () {
            return self::pluck('value', 'key')->toArray();
        });
        return $settings[$key] ?? $default;
    }

    public static function set(string $key, $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget('settings');
    }

    public static function commissionRate(): float
    {
        return (float) self::get('commission_rate', 10);
    }

    public static function payoutDayOfMonth(): int
    {
        return (int) self::get('payout_day_of_month', 5);
    }

    public static function commissionWaitDays(): int
    {
        return (int) self::get('commission_wait_days', 15);
    }

    public static function meetingFee(): float
    {
        return (float) self::get('meeting_fee', 50);
    }

    public static function freelancerMonthlyFee(): float
    {
        return (float) self::get('freelancer_monthly_fee', 299);
    }

    public static function quotesMonthlyFee(): float
    {
        return (float) self::get('quotes_monthly_fee', 199);
    }

    public static function tabelaMonthlyFee(): float
    {
        return (float) self::get('tabela_monthly_fee', 149);
    }

    public static function tabelaMeetingFee(): float
    {
        return (float) self::get('tabela_meeting_fee', 50);
    }

    public static function platformExpenses(): float
    {
        return (float) self::get('platform_expenses', 0);
    }

    public static function contractAcceptanceDays(): int
    {
        return (int) self::get('contract_acceptance_days', 15);
    }
}
