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
                // Log the masked number and a one-line, length-capped reason only — never the message
                // text or the full number, and no new lines that could forge extra log entries.
                $reason = trim(mb_substr((string) preg_replace('/[\r\n\t]+/', ' ', $error), 0, 200));

                $this->warn('SMSTunnel: a new-registration text to ' . $this->mask($to) . ' did not go out: ' . $reason);
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

        // name/username/email are chosen by whoever registers: drop any tags and new lines and clamp each
        // value, so a crafted name can't spoof the text or inflate its length (and cost).
        $clean = static fn ($value): string => trim(mb_substr(
            (string) preg_replace('/\s+/u', ' ', strip_tags((string) $value)),
            0,
            60
        ));

        $replacements = [
            '{name}'     => $clean($user['name'] ?? ''),
            '{username}' => $clean($user['username'] ?? ''),
            '{email}'    => $clean($user['email'] ?? ''),
            '{sitename}' => $clean($this->getApplication()->get('sitename', '')),
        ];

        // Clamp the whole text too, as a backstop against a long template or value.
        return trim(mb_substr(strtr($template, $replacements), 0, 320));
    }

    /**
     * Writes a warning to administrator/logs/plg_user_smstunnel.php. Joomla writes a log category only
     * to the loggers registered for it, so the plugin registers its own file the first time.
     *
     * @param   string  $message
     *
     * @return  void
     */
    private function warn(string $message): void
    {
        static $loggerAdded = false;

        if (!$loggerAdded) {
            Log::addLogger(['text_file' => 'plg_user_smstunnel.php'], Log::ALL, ['plg_user_smstunnel']);
            $loggerAdded = true;
        }

        Log::add($message, Log::WARNING, 'plg_user_smstunnel');
    }

    /**
     * Shows the last three digits of a number only, for the log.
     *
     * @param   string  $number
     *
     * @return  string
     */
    private function mask(string $number): string
    {
        // Fixed width, so the log reveals neither the number nor its length — only the last few digits of
        // a full-length number, enough to tell recipients apart.
        $digits = (string) preg_replace('/\D+/', '', $number);

        return \strlen($digits) >= 7 ? '***' . substr($digits, -3) : '***';
    }
}
