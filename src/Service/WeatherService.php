<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Uses Open-Meteo — 100% free, no API key required.
 * Geocoding: https://geocoding-api.open-meteo.com
 * Weather:   https://api.open-meteo.com
 */
class WeatherService
{
    private const BAD_TEMP_MIN    = 10;
    private const BAD_TEMP_MAX    = 40;
    private const BAD_WIND_SPEED  = 60; // km/h
    private const SEVERE_CODES    = [65, 67, 71, 73, 75, 77, 82, 85, 86, 95, 96, 99];
    private const FORECAST_DAYS   = 7;   // free tier supports up to 16
    private const GEO_URL         = 'https://geocoding-api.open-meteo.com/v1/search';
    private const WEATHER_URL     = 'https://api.open-meteo.com/v1/forecast';

    public function __construct(private HttpClientInterface $httpClient) {}

    /**
     * Returns weather array for the given location and date, or null on failure.
     * Events within FORECAST_DAYS → uses hourly forecast slot closest to event time.
     * Events further away        → uses current weather as a general indicator.
     */
    public function getWeather(string $lieu, \DateTimeInterface $date): ?array
    {
        try {
            $city   = $this->extractCity($lieu);
            $coords = $this->geocode($city);
            if (!$coords) {
                return null;
            }
            return $this->fetchWeather($coords, $date);
        } catch (\Throwable) {
            return null;
        }
    }

    public function isBadWeather(array $weather): bool
    {
        if ($weather['temperature'] < self::BAD_TEMP_MIN || $weather['temperature'] > self::BAD_TEMP_MAX) {
            return true;
        }
        if (\in_array($weather['weather_code'] ?? 0, self::SEVERE_CODES, true)) {
            return true;
        }
        if (($weather['wind_speed'] ?? 0) > self::BAD_WIND_SPEED) {
            return true;
        }
        return false;
    }

    public function getBadWeatherReason(array $weather): string
    {
        if ($weather['temperature'] < self::BAD_TEMP_MIN) {
            return \sprintf(
                'Température trop basse (%d°C, minimum requis : %d°C)',
                $weather['temperature'],
                self::BAD_TEMP_MIN
            );
        }
        if ($weather['temperature'] > self::BAD_TEMP_MAX) {
            return \sprintf(
                'Température trop élevée (%d°C, maximum toléré : %d°C)',
                $weather['temperature'],
                self::BAD_TEMP_MAX
            );
        }
        if (\in_array($weather['weather_code'] ?? 0, self::SEVERE_CODES, true)) {
            return \sprintf('Conditions météo sévères : %s', $weather['description']);
        }
        return \sprintf('Vent trop fort (%d km/h, maximum toléré : %d km/h)', $weather['wind_speed'], self::BAD_WIND_SPEED);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function extractCity(string $lieu): string
    {
        $parts = explode(',', $lieu);
        return trim(end($parts));
    }

    /** Returns ['lat', 'lon', 'name'] or null if city not found. */
    private function geocode(string $city): ?array
    {
        $response = $this->httpClient->request('GET', self::GEO_URL, [
            'query' => [
                'name'     => $city,
                'count'    => 1,
                'language' => 'fr',
                'format'   => 'json',
            ],
        ]);

        $data = $response->toArray();

        if (empty($data['results'])) {
            return null;
        }

        return [
            'lat'  => $data['results'][0]['latitude'],
            'lon'  => $data['results'][0]['longitude'],
            'name' => $data['results'][0]['name'],
        ];
    }

    private function fetchWeather(array $coords, \DateTimeInterface $date): array
    {
        $response = $this->httpClient->request('GET', self::WEATHER_URL, [
            'query' => [
                'latitude'       => $coords['lat'],
                'longitude'      => $coords['lon'],
                'current'        => 'temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code',
                'hourly'         => 'temperature_2m,apparent_temperature,relative_humidity_2m,wind_speed_10m,weather_code',
                'forecast_days'  => self::FORECAST_DAYS,
                'timezone'       => 'auto',
            ],
        ]);

        $data = $response->toArray();
        $now  = new \DateTime();

        $diffSeconds = $date->getTimestamp() - $now->getTimestamp();
        $isForecastRange = $date > $now && $diffSeconds <= self::FORECAST_DAYS * 86400;

        if ($isForecastRange) {
            return $this->buildFromForecast($data, $coords['name'], $date);
        }

        return $this->buildFromCurrent($data, $coords['name']);
    }

    private function buildFromCurrent(array $data, string $cityName): array
    {
        $c = $data['current'];
        return [
            'temperature'  => (int) round($c['temperature_2m']),
            'feels_like'   => (int) round($c['apparent_temperature']),
            'description'  => $this->describe($c['weather_code']),
            'emoji'        => $this->emoji($c['weather_code']),
            'weather_code' => (int) $c['weather_code'],
            'city'         => $cityName,
            'humidity'     => (int) $c['relative_humidity_2m'],
            'wind_speed'   => (int) round($c['wind_speed_10m']),
            'is_forecast'  => false,
        ];
    }

    private function buildFromForecast(array $data, string $cityName, \DateTimeInterface $date): array
    {
        $target  = $date->getTimestamp();
        $times   = $data['hourly']['time'];
        $closest = 0;
        $minDiff = PHP_INT_MAX;

        foreach ($times as $i => $timeStr) {
            $diff = abs((new \DateTime($timeStr))->getTimestamp() - $target);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $i;
            }
        }

        $h = $data['hourly'];
        return [
            'temperature'  => (int) round($h['temperature_2m'][$closest]),
            'feels_like'   => (int) round($h['apparent_temperature'][$closest]),
            'description'  => $this->describe($h['weather_code'][$closest]),
            'emoji'        => $this->emoji($h['weather_code'][$closest]),
            'weather_code' => (int) $h['weather_code'][$closest],
            'city'         => $cityName,
            'humidity'     => (int) $h['relative_humidity_2m'][$closest],
            'wind_speed'   => (int) round($h['wind_speed_10m'][$closest]),
            'is_forecast'  => true,
        ];
    }

    /** WMO weather interpretation codes → French description */
    private function describe(int $code): string
    {
        return match (true) {
            $code === 0                              => 'Ciel dégagé',
            $code === 1                              => 'Principalement dégagé',
            $code === 2                              => 'Partiellement nuageux',
            $code === 3                              => 'Couvert',
            \in_array($code, [45, 48], true)         => 'Brouillard',
            \in_array($code, [51, 53, 55], true)     => 'Bruine',
            \in_array($code, [56, 57], true)         => 'Bruine verglaçante',
            \in_array($code, [61, 63, 65], true)     => 'Pluie',
            \in_array($code, [66, 67], true)         => 'Pluie verglaçante',
            \in_array($code, [71, 73, 75], true)     => 'Neige',
            $code === 77                              => 'Grains de neige',
            \in_array($code, [80, 81, 82], true)     => 'Averses de pluie',
            \in_array($code, [85, 86], true)         => 'Averses de neige',
            \in_array($code, [95, 96, 99], true)     => 'Orage',
            default                                  => 'Variable',
        };
    }

    /** WMO codes → emoji */
    private function emoji(int $code): string
    {
        return match (true) {
            $code === 0                                      => '☀️',
            \in_array($code, [1, 2], true)                   => '⛅',
            $code === 3                                      => '☁️',
            \in_array($code, [45, 48], true)                 => '🌫️',
            \in_array($code, [51, 53, 55, 56, 57], true)    => '🌦️',
            \in_array($code, [61, 63, 65, 66, 67], true)    => '🌧️',
            \in_array($code, [71, 73, 75, 77, 85, 86], true) => '❄️',
            \in_array($code, [80, 81, 82], true)             => '🌧️',
            \in_array($code, [95, 96, 99], true)             => '⛈️',
            default                                          => '🌤️',
        };
    }
}
