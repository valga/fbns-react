<?php

declare(strict_types=1);

namespace Fbns\Network;

class NetworkType
{
    public const NONE = -1;
    public const MOBILE = 0;
    public const WIFI = 1;
    public const MOBILE_MMS = 2;
    public const MOBILE_SUPL = 3;
    public const MOBILE_DUN = 4;
    public const MOBILE_HIPRI = 5;
    public const WIMAX = 6;
    public const BLUETOOTH = 7;
    public const DUMMY = 8;
    public const ETHERNET = 9;
    public const MOBILE_FOTA = 10;
    public const MOBILE_IMS = 11;
    public const MOBILE_CBS = 12;
    public const WIFI_P2P = 13;
    public const MOBILE_IA = 14;
    public const MOBILE_EMERGENCY = 15;
    public const PROXY = 16;
    public const VPN = 17;
}
