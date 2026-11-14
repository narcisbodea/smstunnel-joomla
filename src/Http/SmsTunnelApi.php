<?php

/**
 * @package     SMSTunnel for Joomla
 * @copyright   (C) 2026 NARBOWEB SRL <https://smstunnel.io>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Narboweb\Plugin\User\Smstunnel\Http;

\defined('_JEXEC') or die;

use Joomla\CMS\Http\HttpFactory;

/**
 * A thin client for the SMSTunnel send API (https://smstunnel.io/api/v1/sms/send).
 *
 * Requests go through Joomla's own HTTP factory, so the site's transport settings apply. The API key
 * travels only to smstunnel.io, in the X-API-Key header, over HTTPS.
 */
final class SmsTunnelApi
{
    private const BASE_URL = 'https://smstunnel.io';

    public function __construct(private readonly string $apiKey)
    {
    }

    /**
     * Send one text to one recipient.
     *
     * @param   string  $to           The recipient, international (+40...) or local with a country code.
     * @param   string  $message      The text.
     * @param   string  $deviceId     The phone that sends it, or '' to let SMSTunnel pick one.
     * @param   string  $countryCode  Completes a local number written without + or 00 (e.g. +40), or ''.
     *
     * @return  array{0: bool, 1: string}  [true, ''] on success, [false, reason] otherwise.
     */
    public function send(string $to, string $message, string $deviceId = '', string $countryCode = ''): array
    {
        $body = ['to' => $to, 'message' => $message];

        if ($deviceId !== '') {
            $body['deviceId'] = $deviceId;
        }

        if ($countryCode !== '') {
            $body['defaultCountryCode'] = $countryCode;
        }

        try {
            $http     = (new HttpFactory())->getHttp();
            $response = $http->post(
                self::BASE_URL . '/api/v1/sms/send',
                json_encode($body),
                [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'X-API-Key'    => $this->apiKey,
                ],
                15
            );
        } catch (\Throwable $e) {
            return [false, $e->getMessage()];
        }

        $code = (int) $response->code;
        $data = json_decode((string) $response->body, true);

        if ($code >= 200 && $code < 300 && \is_array($data) && !empty($data['success'])) {
            return [true, ''];
        }

        return [false, $this->errorText($code, $data)];
    }

    /**
     * A short reason for a refused request, from SMSTunnel's error body.
     *
     * @param   int    $code  The HTTP status.
     * @param   mixed  $data  The decoded answer.
     *
     * @return  string
     */
    private function errorText(int $code, $data): string
    {
        if ($code === 401) {
            return 'The API key was rejected (401). Check it in your SMSTunnel dashboard.';
        }

        if (\is_array($data)) {
            foreach (['message', 'error'] as $key) {
                if (empty($data[$key])) {
                    continue;
                }

                if (\is_string($data[$key])) {
                    return trim($data[$key]);
                }

                if (\is_array($data[$key])) {
                    $first = reset($data[$key]);

                    if (\is_string($first) && trim($first) !== '') {
                        return trim($first);
                    }
                }
            }
        }

        return 'SMSTunnel answered with HTTP ' . $code . '.';
    }
}
