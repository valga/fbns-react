<?php

declare(strict_types=1);

namespace Fbns\Client;

use Fbns\Client\Network\NetworkSubtype;
use Fbns\Client\Network\NetworkType;

interface Network
{
    /**
     * Get current network type. Check {@see NetworkType} for available values.
     *
     * @see https://developer.android.com/reference/android/net/ConnectivityManager
     */
    public function type(): int;

    /**
     * Get current network subtype. Check {@see NetworkSubtype} for available values.
     *
     * @see https://developer.android.com/reference/android/telephony/TelephonyManager
     */
    public function subtype(): int;
}
