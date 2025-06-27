=== WordPress Marketing Plugin ===
Contributors: yourname
Donate link: https://yourwebsite.com/
Tags: marketing, whatsapp, marketing-automation, contact-lists, n8n, webhooks
Requires at least: 5.0
Tested up to: 6.4
Stable tag: 1.1.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A multi-user digital marketing plugin for WordPress to manage contact lists, templates, and campaigns via Evolution API with full n8n integration.

== Description ==

WordPress Marketing Plugin functions as a digital marketing hub for multi-users, allowing them to manage contact lists, create message templates, and send communications through Evolution API with n8n as the orchestrator.

The plugin now features complete n8n integration, allowing for advanced workflow automation, webhook handling, and seamless communication between your WordPress site and external services.

Key Features:

* Multi-user support with data isolation
* Contact list management
* Contact import/export functionality
* Message template creation with variable support
* Campaign scheduling and execution
* WhatsApp QR code generation
* Complete n8n integration with webhook processing
* Automated campaign status updates via webhooks
* Contact synchronization with external systems
* Secure API endpoints for third-party integrations

== Installation ==

1. Upload `wp-marketing-plugin.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure plugin settings
4. Set up n8n integration

== Frequently Asked Questions ==

= How do I set up the n8n integration? =

Navigate to the plugin settings page and enter your n8n instance URL along with any required credentials.

= Can users see other users' contact lists and campaigns? =

No, the plugin implements strict data isolation. Each user can only access and manage their own lists, contacts, templates, and campaigns.

== Screenshots ==

1. Dashboard overview
2. Contact list management
3. Message template editor
4. Campaign creation interface
5. QR code generator

== Changelog ==

= 1.1.0 =
* Added complete n8n integration
* Implemented webhook processing system
* Added campaign status update webhook handling
* Added contact synchronization capabilities
* Added QR code generation webhook handling
* Implemented secure webhook registration
* Added contact management API methods

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.1.0 =
This version adds complete n8n integration with webhook processing, contact synchronization, and improved campaign management. Upgrade recommended for all users.

= 1.0.0 =
Initial version of the plugin.