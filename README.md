# SMSTunnel for Joomla

Get a text when a new user registers on your Joomla site — sent from **your own Android phone**, from
**your own number**, through [SMSTunnel](https://smstunnel.io).

When someone signs up, your site hands the text to your SMSTunnel account and your paired phone sends it
from its own SIM, so you see a number you know. There is no per-message carrier fee: the text goes out
from your phone's own plan.

**[Get the SMSTunnel app on Google Play](https://play.google.com/store/apps/details?id=io.smstunnel.app)**

- **Type:** user plugin · element `smstunnel` (`plg_user_smstunnel`)
- **Requires:** Joomla 4.4 or newer, or Joomla 5; PHP 8.1+
- **Needs:** an SMSTunnel account, the free SMSTunnel app on an Android phone, and an API key

## Install

In Joomla: **System → Install → Extensions**, upload `plg_user_smstunnel.zip`, then enable
**User - SMSTunnel** under **System → Plugins** and open its options.

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
- A text that does not go out is logged in the `plg_user_smstunnel` category with the number **masked**
  and **without the message text**; registration is never interrupted.

## Responsible use

Send only texts people expect — notifications to yourself or your team. Unsolicited texts from a personal
SIM break most carriers' terms. Follow your carrier's terms and local rules (in the EU, GDPR and the
ePrivacy rules).

## License

GPL-2.0-or-later, the same as Joomla.

---

SMSTunnel is a product of NARBOWEB SRL. This extension is a third-party integration and is not affiliated
with, endorsed by, or sponsored by Open Source Matters or the Joomla! project. "Joomla!" is a trademark of
Open Source Matters, Inc.
