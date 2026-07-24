<?php

namespace App\Services;

use Illuminate\Http\Request;

class DeviceFingerprintService
{
    public function fromRequest(Request $request): array
    {
        $ua              = $request->userAgent() ?? '';
        $lang            = $request->header('Accept-Language') ?? '';
        $platform        = $request->header('Sec-CH-UA-Platform') ?? '';
        $mobile          = $request->header('Sec-CH-UA-Mobile') ?? '';
        $clientHintModel = $request->header('Sec-CH-UA-Model') ?? '';
        $ip              = $request->ip() ?? '';
        $timezone        = (string) ($request->input('timezone') ?? '');
        $resolution      = (string) ($request->input('screen_resolution') ?? '');
        $canvas          = (string) ($request->input('canvas_hash') ?? '');
        $gpu             = (string) ($request->input('gpu_renderer') ?? '');
        $cpuCores        = (string) ($request->input('cpu_cores') ?? '');
        $devMemory       = (string) ($request->input('device_memory') ?? '');

        $ipPrefix    = implode('.', array_slice(explode('.', $ip), 0, 2));
        $fingerprint = md5($ua . $lang . $platform . $ipPrefix . $timezone . $resolution . $canvas . $gpu . $cpuCores);

        // Identifica o hardware físico — estável entre browsers, redes e abas anônimas
        $isMobileDevice      = $this->detectMobile($ua, $mobile);
        $hardwareFingerprint = $isMobileDevice
            ? md5($platform . $timezone . $resolution . $cpuCores . $devMemory . $gpu)
            : md5($platform . $timezone . $cpuCores . $devMemory . $gpu);

        return [
            'device_fingerprint'  => $fingerprint,
            'hardware_fingerprint' => $hardwareFingerprint,
            'browser'            => $this->parseBrowser($ua),
            'os'                 => $this->parseOS($ua, $platform),
            'is_mobile'          => $this->detectMobile($ua, $mobile),
            'device_model'       => $this->parseDeviceModel($ua, $clientHintModel),
            'timezone'           => $this->sanitizeTimezone($timezone),
            'screen_resolution'  => $this->sanitizeResolution($resolution),
            'canvas_hash'        => $this->sanitizeHex($canvas),
            'gpu_renderer'       => $this->sanitizeGpu($gpu),
            'cpu_cores'          => is_numeric($cpuCores) && $cpuCores > 0 && $cpuCores <= 128 ? (int) $cpuCores : null,
            'device_memory'      => is_numeric($devMemory) && $devMemory > 0 ? (int) $devMemory : null,
        ];
    }

    private function sanitizeTimezone(string $tz): ?string
    {
        if ($tz === '') return null;
        return preg_match('/^[A-Za-z0-9_\/\-+]+$/', $tz) ? substr($tz, 0, 100) : null;
    }

    private function sanitizeResolution(string $res): ?string
    {
        if ($res === '') return null;
        return preg_match('/^\d{3,5}x\d{3,5}$/', $res) ? $res : null;
    }

    private function sanitizeHex(string $val): ?string
    {
        if ($val === '') return null;
        return preg_match('/^[a-f0-9]{1,16}$/i', $val) ? strtolower($val) : null;
    }

    private function sanitizeGpu(string $gpu): ?string
    {
        if ($gpu === '') return null;
        $clean = preg_replace('/[\x00-\x1F\x7F]/', '', $gpu);
        return substr($clean, 0, 255) ?: null;
    }

    private function parseBrowser(string $ua): string
    {
        $browsers = [
            'Edg'     => 'Edge',
            'OPR'     => 'Opera',
            'Chrome'  => 'Chrome',
            'Firefox' => 'Firefox',
            'Safari'  => 'Safari',
            'MSIE'    => 'IE',
            'Trident' => 'IE',
        ];

        foreach ($browsers as $key => $name) {
            if (str_contains($ua, $key)) {
                if (preg_match("/{$key}\/([0-9]+)/", $ua, $m)) {
                    return "{$name} {$m[1]}";
                }
                return $name;
            }
        }

        return 'Desconhecido';
    }

    private function parseOS(string $ua, string $platform = ''): string
    {
        // Sec-CH-UA-Platform é a fonte mais confiável — não muda com modo desktop
        $p = strtolower(trim($platform, '"\''));
        if ($p !== '') {
            return match ($p) {
                'android'  => 'Android',
                'ios'      => str_contains($ua, 'iPad') ? 'iOS (iPad)' : 'iOS (iPhone)',
                'windows'  => $this->parseWindowsVersion($ua),
                'macos'    => 'macOS',
                'linux'    => 'Linux',
                'chromeos' => 'ChromeOS',
                default    => ucfirst($p),
            };
        }

        // Fallback: User-Agent (menos confiável em modo desktop)
        return match (true) {
            str_contains($ua, 'Windows NT 10') => 'Windows 10/11',
            str_contains($ua, 'Windows NT 6')  => 'Windows 7/8',
            str_contains($ua, 'Windows')       => 'Windows',
            str_contains($ua, 'iPhone')        => 'iOS (iPhone)',
            str_contains($ua, 'iPad')          => 'iOS (iPad)',
            str_contains($ua, 'Android')       => 'Android',
            str_contains($ua, 'Mac OS X')      => 'macOS',
            str_contains($ua, 'Linux')         => 'Linux',
            default                            => 'Desconhecido',
        };
    }

    private function parseWindowsVersion(string $ua): string
    {
        if (str_contains($ua, 'Windows NT 10')) return 'Windows 10/11';
        if (str_contains($ua, 'Windows NT 6'))  return 'Windows 7/8';
        return 'Windows';
    }

    private function detectMobile(string $ua, string $mobile = ''): bool
    {
        // Sec-CH-UA-Mobile é a fonte mais confiável — funciona mesmo em modo desktop
        if ($mobile !== '') {
            return $mobile === '?1';
        }

        // Fallback: User-Agent
        return (bool) preg_match('/Mobile|Android|iPhone|iPad|Windows Phone/i', $ua);
    }

    private function parseDeviceModel(string $ua, string $clientHintModel): ?string
    {
        // Sec-CH-UA-Model (Chrome no Android envia automaticamente após Accept-CH)
        $model = trim($clientHintModel, '"\'');
        if ($model !== '' && strtolower($model) !== 'unknown') {
            return $this->resolveMarketingName($model);
        }

        // iOS — Apple não expõe o modelo exato via navegador
        if (str_contains($ua, 'iPhone')) return 'iPhone';
        if (str_contains($ua, 'iPad'))   return 'iPad';

        // Android — modelo fica entre "Android X.X; " e ")" ou "Build/"
        if (preg_match('/\(Linux;\s*Android\s+[\d.]+;\s*([^;)]+)/i', $ua, $m)) {
            $name = preg_replace('/\s+Build\/.*/i', '', trim($m[1]));
            $name = trim($name);
            // Android 13+ substitui o modelo por "K" (privacidade) — ignorar
            if ($name === '' || strlen($name) <= 1) return null;
            return $this->resolveMarketingName($name);
        }

        return null;
    }

    private function resolveMarketingName(string $code): string
    {
        $code = substr(trim($code), 0, 100);

        $lookup = [
            // Samsung Galaxy S
            'SM-S928B' => 'Samsung Galaxy S24 Ultra',
            'SM-S926B' => 'Samsung Galaxy S24+',
            'SM-S921B' => 'Samsung Galaxy S24',
            'SM-S918B' => 'Samsung Galaxy S23 Ultra',
            'SM-S916B' => 'Samsung Galaxy S23+',
            'SM-S911B' => 'Samsung Galaxy S23',
            'SM-S908B' => 'Samsung Galaxy S22 Ultra',
            'SM-S906B' => 'Samsung Galaxy S22+',
            'SM-S901B' => 'Samsung Galaxy S22',
            // Samsung Galaxy A
            'SM-A556B' => 'Samsung Galaxy A55',
            'SM-A546B' => 'Samsung Galaxy A54',
            'SM-A536B' => 'Samsung Galaxy A53',
            'SM-A346B' => 'Samsung Galaxy A34',
            'SM-A336B' => 'Samsung Galaxy A33',
            'SM-A256B' => 'Samsung Galaxy A25',
            'SM-A245M' => 'Samsung Galaxy A24',
            'SM-A156B' => 'Samsung Galaxy A15',
            'SM-A135M' => 'Samsung Galaxy A13',
            'SM-A057M' => 'Samsung Galaxy A05s',
            'SM-A045M' => 'Samsung Galaxy A04s',
            'SM-A035M' => 'Samsung Galaxy A03s',
            'SM-A325M' => 'Samsung Galaxy A32',
            'SM-A225M' => 'Samsung Galaxy A22',
            'SM-A125M' => 'Samsung Galaxy A12',
            // Xiaomi Redmi Note
            '23106RN0DA' => 'Xiaomi Redmi Note 13',
            '23090RA98G' => 'Xiaomi Redmi Note 13',
            '22111317I'  => 'Xiaomi Redmi Note 11S',
            '22120RN86G' => 'Xiaomi Redmi Note 12S',
            '23049RAD8G' => 'Xiaomi Redmi Note 12',
            '22101316G'  => 'Xiaomi Redmi Note 11',
            '22101317G'  => 'Xiaomi Redmi Note 11',
            '2201116SG'  => 'Xiaomi Redmi Note 11',
            'M2101K6G'   => 'Xiaomi Redmi Note 10 5G',
            'M2101K6I'   => 'Xiaomi Redmi Note 10 5G',
            '2107113SI'  => 'Xiaomi Redmi Note 10S',
            'M2003J15SC' => 'Xiaomi Redmi Note 9S',
            'M2002J9G'   => 'Xiaomi Redmi Note 9',
            // Xiaomi Redmi
            '23106RN0DG' => 'Xiaomi Redmi 13C',
            '220733SFG'  => 'Xiaomi Redmi 10C',
            'M2006C3MG'  => 'Xiaomi Redmi 9C',
            // Xiaomi outros
            '2304FPN6DG' => 'Xiaomi 13T',
            '22081212UG' => 'Xiaomi 12T',
            // Motorola
            'moto g84'   => 'Motorola Moto G84',
            'moto g54'   => 'Motorola Moto G54',
            'moto g34'   => 'Motorola Moto G34',
            'moto g14'   => 'Motorola Moto G14',
            'moto g13'   => 'Motorola Moto G13',
            'moto g32'   => 'Motorola Moto G32',
            'moto g22'   => 'Motorola Moto G22',
            'moto g200'  => 'Motorola Moto G200',
            // LG
            'LM-Q730'    => 'LG K62',
            'LM-K420'    => 'LG K42',
        ];

        $key = strtoupper($code);
        foreach ($lookup as $raw => $name) {
            if (strtoupper($raw) === $key) {
                return $name;
            }
        }

        if (preg_match('/^SM-/i', $code))  return 'Samsung ' . $code;
        if (preg_match('/^moto /i', $code)) return 'Motorola ' . $code;

        return $code;
    }
}
