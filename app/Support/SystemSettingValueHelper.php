<?php

namespace App\Support;

class SystemSettingValueHelper
{
    public static function portalMinRechargeAmount($setting = null): int
    {
        $amount = (int) SystemSettingRuntimeStore::get('portal_min_recharge_amount', $setting->portal_min_recharge_amount ?? 100000);
        return $amount > 0 ? $amount : 100000;
    }

    public static function vipDiscountEnabled($setting = null): bool
    {
        return (int) ($setting->vip_discount ?? 0) === 1;
    }

    public static function vipDiscountPercentage($setting = null): float
    {
        return self::normalizePercentage(SystemSettingRuntimeStore::get('vip_discount_percentage', $setting->vip_discount_percentage ?? 50), 50);
    }

    public static function rechargeOfferRewardEnabled($setting = null): bool
    {
        return (int) ($setting->recharge_offer_reward ?? 0) === 1;
    }

    public static function rechargeOfferRewardPercentage($setting = null): float
    {
        return self::normalizePercentage(SystemSettingRuntimeStore::get('recharge_offer_reward_percentage', $setting->recharge_offer_reward_percentage ?? 5), 5);
    }

    public static function recallPortalPercentage($setting = null): float
    {
        return self::normalizePercentage(SystemSettingRuntimeStore::get('recall_portal_percentage', $setting->recall_portal_percentage ?? 70), 70);
    }

    public static function recallCompanyPercentage($setting = null): float
    {
        return self::normalizePercentage(SystemSettingRuntimeStore::get('recall_company_percentage', $setting->recall_company_percentage ?? 30), 30);
    }

    public static function recallCompanyUserId($setting = null): ?int
    {
        $userId = (int) SystemSettingRuntimeStore::get('recall_company_user_id', $setting->recall_company_user_id ?? 1);
        return $userId > 0 ? $userId : null;
    }

    public static function withdrawDayRequirement($setting = null): int
    {
        $count = (int) SystemSettingRuntimeStore::get('withdraw_day_requirement', $setting->withdraw_day_requirement ?? 0);
        return $count >= 0 ? $count : 0;
    }

    public static function withdrawBlockedDays($setting = null): array
    {
        $days = self::parseIntegerCsv((string) SystemSettingRuntimeStore::get('withdraw_blocked_days', $setting->withdraw_blocked_days ?? '22,23,24,25,26,27,28,29,30'), 1, 31);
        if (empty($days)) {
            $days = [22, 23, 24, 25, 26, 27, 28, 29, 30];
        }

        sort($days);
        return array_values(array_unique($days));
    }

    public static function withdrawAllowedAmounts($setting = null, array $fallback = []): array
    {
        $fallback = !empty($fallback) ? $fallback : self::defaultWithdrawAmounts();
        $amounts = self::parseIntegerCsv((string) SystemSettingRuntimeStore::get('withdraw_allowed_amounts', $setting->withdraw_allowed_amounts ?? ''), 1, PHP_INT_MAX);

        if (empty($amounts)) {
            return $fallback;
        }

        sort($amounts);
        return array_values(array_unique($amounts));
    }

    public static function withdrawScopeType($setting = null): string
    {
        $scope = trim((string) SystemSettingRuntimeStore::get('withdraw_scope_type', $setting->withdraw_scope_type ?? 'all_hosts'));
        $allowed = ['all_hosts', 'agency_hosts', 'all_agency_owners'];

        return in_array($scope, $allowed, true) ? $scope : 'all_hosts';
    }

    public static function withdrawScopeAgencyId($setting = null): ?int
    {
        $agencyId = (int) SystemSettingRuntimeStore::get('withdraw_scope_agency_id', $setting->withdraw_scope_agency_id ?? 0);
        return $agencyId > 0 ? $agencyId : null;
    }

    public static function withdrawAllowedUserIds($setting = null): array
    {
        return self::parseIntegerCsv((string) SystemSettingRuntimeStore::get('withdraw_allowed_user_ids', $setting->withdraw_allowed_user_ids ?? ''), 1, PHP_INT_MAX);
    }

    public static function withdrawBlockedUserIds($setting = null): array
    {
        return self::parseIntegerCsv((string) SystemSettingRuntimeStore::get('withdraw_blocked_user_ids', $setting->withdraw_blocked_user_ids ?? ''), 1, PHP_INT_MAX);
    }

    public static function baseVipPackages(): array
    {
        return [
            ['vip_no' => 1, 'normal_price' => 1000000],
            ['vip_no' => 2, 'normal_price' => 2000000],
            ['vip_no' => 3, 'normal_price' => 3000000],
            ['vip_no' => 4, 'normal_price' => 4000000],
            ['vip_no' => 5, 'normal_price' => 6000000],
            ['vip_no' => 6, 'normal_price' => 8000000],
            ['vip_no' => 7, 'normal_price' => 10000000],
        ];
    }

    public static function applyVipDiscountToPackages(array $packages, $setting = null): array
    {
        $multiplier = self::vipDiscountEnabled($setting)
            ? max(0, 1 - (self::vipDiscountPercentage($setting) / 100))
            : 1;

        return array_values(array_map(function ($package) use ($multiplier) {
            $normalPrice = (int) ($package['normal_price'] ?? 0);
            $discountPrice = (int) floor($normalPrice * $multiplier);

            return [
                'vip_no' => (int) ($package['vip_no'] ?? 0),
                'normal_price' => (string) $normalPrice,
                'discount_price' => (string) max(0, $discountPrice),
            ];
        }, $packages));
    }

    public static function determineVipLevelFromRecharge(int $amount, $setting = null): int
    {
        $packages = self::baseVipPackages();
        $multiplier = self::vipDiscountEnabled($setting)
            ? max(0, 1 - (self::vipDiscountPercentage($setting) / 100))
            : 1;

        $matchedVip = 0;
        foreach ($packages as $package) {
            $threshold = (int) floor(((int) $package['normal_price']) * $multiplier);
            if ($amount >= $threshold) {
                $matchedVip = (int) $package['vip_no'];
            }
        }

        return $matchedVip;
    }

    public static function defaultWithdrawAmounts(): array
    {
        return [300000, 500000, 700000, 1000000, 1500000, 2000000, 3000000, 4000000, 5000000, 6500000, 8000000, 10000000];
    }

    public static function parseIntegerCsv(?string $value, int $min = 1, int $max = PHP_INT_MAX): array
    {
        $value = trim((string) $value);
        if ($value === '') {
            return [];
        }

        $items = preg_split('/[\s,]+/', $value, -1, PREG_SPLIT_NO_EMPTY);
        $numbers = [];
        foreach ($items as $item) {
            if (!is_numeric($item)) {
                continue;
            }

            $number = (int) $item;
            if ($number < $min || $number > $max) {
                continue;
            }

            $numbers[] = $number;
        }

        return array_values(array_unique($numbers));
    }

    private static function normalizePercentage($value, float $fallback): float
    {
        if (!is_numeric($value)) {
            return $fallback;
        }

        $percentage = round((float) $value, 2);
        if ($percentage < 0) {
            return 0.0;
        }

        if ($percentage > 100) {
            return 100.0;
        }

        return $percentage;
    }
}
