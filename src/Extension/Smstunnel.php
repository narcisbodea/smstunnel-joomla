<?php

/**
 * @package     SMSTunnel for Joomla
 * @copyright   (C) 2026 NARBOWEB SRL <https://smstunnel.io>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Narboweb\Plugin\User\Smstunnel\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Log\Log;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Narboweb\Plugin\User\Smstunnel\Http\SmsTunnelApi;

/**
 * Texts you through SMSTunnel when a new user registers on your Joomla site, from your own Android
 * phone and your own number.
 */
final class Smstunnel extends CMSPlugin implements SubscriberInterface
{
    /** @var bool Load this plugin's language strings automatically. */
    protected $autoloadLanguage = true;

    public static function getSubscribedEvents(): array
    {
        return ['onUserAfterSave' => 'onUserAfterSave'];
    }

    /**
     * A user record was saved. On a new one that saved cleanly, text the configured recipients.
     *
     * Positional arguments work on both Joomla 4 (a generic Event) and Joomla 5 (a concrete
     * AfterSaveEvent): user data array, isNew, savingResult, (errorMessage).
     *
     * @param   Event  $event
     *
     * @return  void
     */
    public function onUserAfterSave(Event $event): void
    {
        [$user, $isNew, $success] = array_values($event->getArguments()) + [null, null, null];

        if (!$isNew || !$success || !\is_array($user)) {
            return;
        }

        // Optionally only self-registrations from the site, not accounts an admin creates.
        if ((int) $this->params->get('siteonly', 0) === 1 && !$this->getApplication()->isClient('site')) {
            return;
        }

        $apiKey     = trim((string) $this->params->get('apikey', ''));
        $recipients = $this->recipients();

        if ($apiKey === '' || $recipients === []) {
            return;
        }

        $message = $this->buildMessage($user);

        if ($message === '') {
            return;
        }

        $deviceId    = trim((string) $this->params->get('deviceid', ''));
        $countryCode = trim((string) $this->params->get('countrycode', ''));
        $api         = new SmsTunnelApi($apiKey);

        foreach ($recipients as $to) {
            [$sent, $error] = $api->send($to, $message, $deviceId, $countryCode);

            if (!$sent) {
                // Log the masked number and the reason only — never the message text or the full number.
                Log::add(
                    'SMSTunnel: a new-registration text to ' . $this->mask($to) . ' did not go out: ' . $error,
                    Log::WARNING,
                    'plg_user_smstunnel'
                );
            }
        }
    }

    /**
     * The recipient numbers from the plugin settings, split on commas, semicolons and new lines.
     *
     * @return  string[]
     */
    private function recipients(): array
    {
        $parts = preg_split('/[,;\r\n]+/', (string) $this->params->get('recipients', '')) ?: [];
        $out   = [];

        foreach ($parts as $part) {
            $part = trim($part);

            if ($part !== '' && !\in_array($part, $out, true)) {
                $out[] = $part;
            }
        }

        return $out;
    }

    /**
     * The text to send, from the template with the new user's details filled in.
     *
     * @param   array  $user
     *
     * @return  string
     */
    private function buildMessage(array $user): string
    {
        $template = (string) $this->params->get('template', 'New registration on {sitename}: {name} ({email})');

        $replacements = [
            '{name}'     => (string) ($user['name'] ?? ''),
            '{username}' => (string) ($user['username'] ?? ''),
            '{email}'    => (string) ($user['email'] ?? ''),
            '{sitename}' => (string) $this->getApplication()->get('sitename', ''),
        ];

        return trim(strtr($template, $replacements));
    }

    /**
     * Shows the last four digits of a number only, for the log.
     *
     * @param   string  $number
     *
     * @return  string
     */
    private function mask(string $number): string
    {
        $length = \strlen($number);

        return $length > 4 ? str_repeat('*', $length - 4) . substr($number, -4) : '****';
    }
}
