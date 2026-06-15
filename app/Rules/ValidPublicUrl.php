<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidPublicUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (app()->environment('local') || config('app.debug')) {
            return;
        }

        if (!is_string($value)) {
            $fail('The :attribute must be a valid string.');
            return;
        }

        $host = parse_url($value, PHP_URL_HOST);
        if (!$host) {
            $fail('The :attribute does not contain a valid host name.');
            return;
        }

        // Check if the host itself is an IP, or resolve it
        $ips = [];
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            $ips[] = $host;
        } else {
            // Retrieve IP addresses associated with host (both IPv4 and IPv6 if possible)
            $records = @dns_get_record($host, DNS_A | DNS_AAAA);
            if ($records) {
                foreach ($records as $record) {
                    if (isset($record['ip'])) {
                        $ips[] = $record['ip'];
                    } elseif (isset($record['ipv6'])) {
                        $ips[] = $record['ipv6'];
                    }
                }
            }
            if (empty($ips)) {
                $ip = @gethostbyname($host);
                if ($ip && $ip !== $host) {
                    $ips[] = $ip;
                }
            }
        }

        if (empty($ips)) {
            $fail('The domain could not be resolved to a valid IP address.');
            return;
        }

        foreach ($ips as $ip) {
            if ($this->isPrivateOrReservedIp($ip)) {
                $fail('Scanning internal, private, or reserved network addresses is not allowed.');
                return;
            }
        }
    }

    protected function isPrivateOrReservedIp(string $ip): bool
    {
        // Check IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $this->isPrivateOrReservedIpv4($ip);
        }

        // Check IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $this->isPrivateOrReservedIpv6($ip);
        }

        return true; // Block unrecognized or invalid IPs to be safe
    }

    protected function isPrivateOrReservedIpv4(string $ip): bool
    {
        $long = ip2long($ip);
        if ($long === false) {
            return true;
        }

        // Check various ranges:
        // 0.0.0.0/8
        if (($long & 0xFF000000) === 0x00000000) return true;
        // 10.0.0.0/8
        if (($long & 0xFF000000) === 0x0A000000) return true;
        // 127.0.0.0/8
        if (($long & 0xFF000000) === 0x7F000000) return true;
        // 169.254.0.0/16
        if (($long & 0xFFFF0000) === 0xA9FE0000) return true;
        // 172.16.0.0/12
        if (($long & 0xFFF00000) === 0xAC100000) return true;
        // 192.168.0.0/16
        if (($long & 0xFFFF0000) === 0xC0A80000) return true;
        // 224.0.0.0/4 (Multicast)
        if (($long & 0xF0000000) === 0xE0000000) return true;
        // 240.0.0.0/4 (Reserved)
        if (($long & 0xF0000000) === 0xF0000000) return true;

        return false;
    }

    protected function isPrivateOrReservedIpv6(string $ip): bool
    {
        $hex = bin2hex(inet_pton($ip));
        if (!$hex) {
            return true;
        }

        // ::1/128 (Loopback)
        if ($hex === str_repeat('0', 31) . '1') return true;
        // ::/128 (Unspecified)
        if ($hex === str_repeat('0', 32)) return true;

        // fc00::/7 (Unique Local Address)
        $firstByteDec = hexdec(substr($hex, 0, 2));
        if (($firstByteDec & 0xFE) === 0xFC) {
            return true;
        }

        // fe80::/10 (Link-Local)
        if (str_starts_with($hex, 'fe8') || str_starts_with($hex, 'fe9') || str_starts_with($hex, 'fea') || str_starts_with($hex, 'feb')) {
            return true;
        }

        return false;
    }
}
