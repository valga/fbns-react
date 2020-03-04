<?php

declare(strict_types=1);

namespace Fbns\Client\Mqtt;

class ClientCapabilities
{
    public const ACKNOWLEDGED_DELIVERY = 0;
    public const PROCESSING_LASTACTIVE_PRESENCEINFO = 1;
    public const EXACT_KEEPALIVE = 2;
    public const REQUIRES_JSON_UNICODE_ESCAPES = 3;
    public const DELTA_SENT_MESSAGE_ENABLED = 4;
    public const USE_ENUM_TOPIC = 5;
    public const SUPPRESS_GETDIFF_IN_CONNECT = 6;
    public const USE_THRIFT_FOR_INBOX = 7;
    public const USE_SEND_PINGRESP = 8;
    public const REQUIRE_REPLAY_PROTECTION = 9;
    public const DATA_SAVING_MODE = 10;
    public const TYPING_OFF_WHEN_SENDING_MESSAGE = 11;
    public const PERMISSION_USER_AUTH_CODE = 12;
    public const FBNS_EXPLICIT_DELIVERY_ACK = 13;
    public const IS_LARGE_PAYLOAD_SUPPORTED = 14;

    public const DEFAULT_SET = 0
        | 1 << self::ACKNOWLEDGED_DELIVERY
        | 1 << self::PROCESSING_LASTACTIVE_PRESENCEINFO
        | 1 << self::EXACT_KEEPALIVE
        | 0 << self::REQUIRES_JSON_UNICODE_ESCAPES
        | 1 << self::DELTA_SENT_MESSAGE_ENABLED
        | 1 << self::USE_ENUM_TOPIC
        | 0 << self::SUPPRESS_GETDIFF_IN_CONNECT
        | 1 << self::USE_THRIFT_FOR_INBOX
        | 0 << self::USE_SEND_PINGRESP
        | 0 << self::REQUIRE_REPLAY_PROTECTION
        | 0 << self::DATA_SAVING_MODE
        | 0 << self::TYPING_OFF_WHEN_SENDING_MESSAGE
        | 0 << self::PERMISSION_USER_AUTH_CODE
        | 0 << self::FBNS_EXPLICIT_DELIVERY_ACK
        | 0 << self::IS_LARGE_PAYLOAD_SUPPORTED;
}
