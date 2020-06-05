<?php


namespace Alimianesa\SmartAuth\Resources\LaravelSmartCoding;


use Carbon\Carbon;

class SmartCoding
{
    public static function timeStampCode(int $randomLen = 8): int
    {
        return (Carbon::now()->timestamp * 10000) + rand(pow(10, $randomLen-1), pow(10, $randomLen ) - 1);
    }

    public static function encodeToBase32(int $input):string
    {
        return base_convert($input, 10, 32);
    }

    public static function decodeToBase32(string $input):int
    {
        return base_convert($input, 32, 10);
    }
}
