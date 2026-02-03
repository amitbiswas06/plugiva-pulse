=== Plugiva Pulse ===
Contributors: amitbiswas06
Tags: feedback, surveys, forms, polls, ajax
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight feedback and pulse collection plugin with admin-managed questions and clean frontend submission.

== Description ==

Plugiva Pulse lets you create simple “pulses” — short feedback or question sets — and collect responses through a clean frontend form.

It is designed to be lightweight, privacy-conscious, and easy to manage from the WordPress admin without relying on third-party services.

### Key Features

* Create and manage pulses from the admin panel
* Public or private pulse visibility
* Frontend submission via block or shortcode
* AJAX-based submissions (no page reloads)
* Spam protection built in
* Admin responses table with pagination
* Bulk delete responses
* CSV export of collected responses
* Clean uninstall (optional data removal)

### Use Cases

* Quick site feedback
* One-question or multi-question surveys
* User experience check-ins
* Lightweight internal polls

Plugiva Pulse is intentionally focused. It does not try to be a full form builder.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugiva-pulse` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Plugiva Pulse → Pulses** to create your first pulse.

== Usage ==

### Block
Use the **Plugiva Pulse** block in the block editor and select a pulse.

### Shortcode

`[ppls_pulse id="pulse_id_here"]`

Replace `pulse_id_here` with the pulse ID shown in the admin panel.

== Frequently Asked Questions ==

= Does this plugin store data externally? =
No. All data is stored locally in your WordPress database.

= Does it support CSV export? =
Yes. Responses can be exported as CSV from the admin interface.

= Is this GDPR-friendly? =
Plugiva Pulse does not collect personal data by default. You are responsible for how you configure and use it.

= Can I remove all data on uninstall? =
Yes. The plugin includes a clean uninstall routine to remove stored data.

== Screenshots ==

1. Pulse list in admin
2. Pulse editor
3. Frontend pulse form
4. Responses table
5. CSV export

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial public release.
