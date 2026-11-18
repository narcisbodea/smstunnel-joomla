# SMSTunnel for Joomla

Get a text when a new user registers on your Joomla site — sent from **your own Android phone**, from
**your own number**, through [SMSTunnel](https://smstunnel.io).

When someone signs up, your site hands the text to your SMSTunnel account and your paired phone sends it
from its own SIM, so you see a number you know. There is no per-message carrier fee: the text goes out
from your phone's own plan.

**[Get the SMSTunnel app on Google Play](https://play.google.com/store/apps/details?id=io.smstunnel.app)**

- **Type:** user plugin · element `smstunnel` (`plg_user_smstunnel`)
- **Requires:** Joomla 4.4, 5 or 6; PHP 8.1+ (tested on Joomla 5.4.10 with PHP 8.3 and Joomla 6.2.0 with PHP 8.4)
- **Needs:** an SMSTunnel account, the free SMSTunnel app on an Android phone, and an API key

## Install

Download `plg_user_smstunnel.zip` from the [latest release](https://github.com/narcisbodea/smstunnel-joomla/releases/latest).
In Joomla: **System → Install → Extensions**, upload the zip, then enable **User - SMSTunnel** under
**System → Plugins** and open its options.

New versions show up in Joomla under **System → Update → Extensions**: the plugin has an update server.

## Set it up

1. In your **SMSTunnel dashboard** (smstunnel.io), pair an Android phone in the app, then open
   **Apps → Add an app** and copy its **API key**.
2. In the plugin options, paste the **API key**, set **Text these numbers** (your own number or numbers,
   comma-separated, international format), and optionally a **Default country code**, a **Phone** (to pin
   the texts to one device) and the **Message**.
3. Enable the plugin. Register a test account (or have someone sign up) — you get a text.

## Settings

| Option | What it does |
| --- | --- |
| API key | Your SMSTunnel API key. |
| Text these numbers | Who to notify about a new registration. One text per number. |
| Default country code | Completes local numbers written without `+` or `00` (e.g. `+40`). |
| Phone (optional) | Which paired phone sends the texts; empty = any online phone. |
| Message | The text, with placeholders `{name}`, `{username}`, `{email}`, `{sitename}`. |
| Only self-registrations | Yes = text only when a visitor signs up, not when an admin creates an account. |

## Privacy and security

- The recipient number and the message are sent to SMSTunnel (smstunnel.io), which relays them to your
  paired phone. SMSTunnel's [privacy policy](https://smstunnel.io/privacy) applies.
- Requests go through Joomla's own HTTP client over HTTPS. The API key travels only to smstunnel.io, in
  the `X-API-Key` header, and is never written to the log.
- A text that does not go out is written to `administrator/logs/plg_user_smstunnel.php`, with the number
  **masked** (its last three digits) and **without the message text**, next to SMSTunnel's reason (for
  example a rejected key or too many requests). Registration is never interrupted.

## Responsible use

Send only texts people expect — notifications to yourself or your team. Unsolicited texts from a personal
SIM break most carriers' terms. Follow your carrier's terms and local rules (in the EU, GDPR and the
ePrivacy rules).

## Changelog

- **1.0.1**: works on Joomla 6 (it reads SMSTunnel's answer through PSR-7; Joomla 6 removed the old
  `code` and `body` properties, so 1.0.0 counted every text there as failed). A text that does not go
  out is now written to `administrator/logs/plg_user_smstunnel.php` (1.0.0 wrote it to no log file).
  An update server, so Joomla offers new versions.
- **1.0.0**: first release.

## License

GPL-2.0-or-later, the same as Joomla.

---

SMSTunnel is a product of NARBOWEB SRL. This extension is a third-party integration and is not affiliated
with, endorsed by, or sponsored by Open Source Matters or the Joomla! project. "Joomla!" is a trademark of
Open Source Matters, Inc.
