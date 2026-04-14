=== Plugiva Pulse - Inline Feedback Plugin for WordPress ===
Contributors: amitbiswas06
Tags: feedback, poll, survey, questionnaire, engagement
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 1.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A lightweight inline feedback plugin that blends into your content and captures real user sentiment without disruption.

== Description ==

Plugiva Pulse lets you collect lightweight feedback and quick reactions directly inside your content.

Create structured pulses using yes/no, emoji, or short text questions, or embed inline feedback prompts anywhere using a shortcode. Responses are submitted instantly via AJAX without page reloads.

It is designed to be privacy-conscious, fast, and easy to manage from the WordPress admin without relying on third-party services.

### Key Features

* Create and manage pulses from the admin panel
* Embed inline feedback questions directly inside posts and pages (e.g. "Was this helpful?")
* Support for yes/no, emoji, and custom response types
* AJAX-based submissions (no page reloads)
* Built-in spam protection and duplicate prevention
* Admin responses table with filtering and pagination
* CSV export with post context
* Developer-friendly filters for customization
* Clean uninstall (optional data removal)

### Use Cases

* Quick inline feedback inside blog posts
* One-question reactions (e.g. "Was this helpful?")
* Emoji-based engagement prompts
* Lightweight internal polls

Plugiva Pulse is intentionally focused. It does not try to be a full form builder.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugiva-pulse` directory, or install the plugin through the WordPress plugins screen.
2. Activate the plugin through the ‘Plugins’ screen in WordPress.
3. Go to **Plugiva Pulse → Pulses** to create your first pulse.

== Usage ==

### Inline Feedback (New)

Plugiva Pulse allows you to embed lightweight feedback prompts directly inside your content using a shortcode.

Example:

`[ppls_question q="Was this helpful?" type="yesno"]`

You can also use emoji-based feedback:

`[ppls_question q="How do you feel about this?" type="emoji"]`

Inline feedback is designed for quick engagement:

* No page reloads (AJAX powered)
* Duplicate submissions are prevented automatically
* Feedback is stored with post context
* Works seamlessly inside posts and pages

Custom response types can be added using the developer hooks below.

### Block
Use the **Plugiva Pulse** block in the block editor and select a pulse.

### Shortcodes

#### Pulse

`[ppls_pulse id="pulse_id_here"]`

Replace `pulse_id_here` with the pulse ID shown in the admin panel.

#### Inline Feedback

Use inline feedback to collect quick reactions directly inside your content.

`[ppls_question q="Was this helpful?" type="yesno"]`

You can also use:

`[ppls_question q="How do you feel about this?" type="emoji"]`

### Developer Hooks

Plugiva Pulse provides filters to customize inline feedback behavior and extend response types.

#### ppls_inline_options

Modify or add custom inline question types.

Each type is defined as an array of key → label pairs.

Example:

    add_filter( 'ppls_inline_options', function( $options ) {

        $options['rating'] = [
            '1' => '⭐',
            '2' => '⭐⭐',
            '3' => '⭐⭐⭐',
        ];

        return $options;
    });

Use in shortcode:

`[ppls_question q="Rate this post" type="rating"]`

Note: Validation is handled automatically based on defined options.

#### ppls_inline_feedback

Modify the feedback message shown after submission.

Example:

    add_filter( 'ppls_inline_feedback', function( $feedback ) {

        return [
            'icon' => '✓',
            'text' => 'Thanks for your feedback!',
        ];
    });

#### ppls_inline_hash_window

Control how long an inline session remains valid.

Default is 1 hour.

Example:

    add_filter( 'ppls_inline_hash_window', function( $window ) {
        return 600; // 10 minutes
    });

### Notes

* Labels support safe HTML (sanitized via wp_kses_post)
* Custom types automatically work with validation
* No additional hooks are required for custom answer handling

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

1. Inline feedback inside post
2. Inline feedback after submission
3. Pulse list in admin
4. Pulse editor
5. Frontend pulse form
6. Responses table
7. CSV export

== Changelog ==

= 1.2.1 =
* Improved plugin title and readme for better clarity and discovery
* Minor optimizations

= 1.2.0 =
* New: Inline feedback questions via shortcode (yes/no, emoji, custom types)
* New: Support for custom inline response types using filters
* New: Developer hooks for options, feedback message, and hash window
* Improved: Unified hash system for consistent session handling
* Improved: Inline UX with instant feedback and smooth interactions
* Improved: Duplicate prevention and session validation for inline responses
* Improved: CSV export includes response context (type, source, post)
* Improved: Admin responses table with better filtering support
* Refactor: Centralized inline utilities for options, labels, and validation
* Refactor: Simplified developer API (single source of truth for options)

= 1.1.0 =
* New: Admin "New Responses" bubble in menu
* New: Highlight new responses in admin table
* Improved: Better visibility of incoming feedback

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.2.0 =
Introduces inline feedback and developer customization hooks. Recommended update.
