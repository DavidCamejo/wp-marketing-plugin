# WordPress Marketing Plugin

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](http://www.gnu.org/licenses/gpl-2.0.html)
[![PHP Version Require: 7.4+](https://img.shields.io/badge/PHP-7.4%2B-blue)](https://www.php.net/downloads.php)
[![WordPress Version: 5.0+](https://img.shields.io/badge/WordPress-5.0%2B-brightgreen)](https://wordpress.org/)

## Overview

WordPress Marketing Plugin is a comprehensive multi-user digital marketing solution for WordPress, enabling businesses to manage contact lists, create message templates, and execute marketing campaigns through WhatsApp via Evolution API with n8n integration.

## Key Features

- **Multi-User Management**: Complete user data isolation and access control
- **Contact Management**: Import, export, and organize contact lists
- **Template Creation**: Design reusable message templates with variable support
- **Campaign Scheduling**: Set up and automate campaign delivery
- **WhatsApp Integration**: Connect with the Evolution API for WhatsApp messaging
- **QR Code Generation**: Generate WhatsApp connection QR codes for new instances
- **Complete n8n Integration**: Webhook handling and workflow automation

## n8n Integration Features

- **Webhook Processing System**: Handle incoming webhooks from n8n workflows
- **Campaign Status Updates**: Process campaign status changes via webhooks
- **Contact Synchronization**: Keep contact data synchronized between systems
- **QR Code Processing**: Handle QR code generation and management via webhooks
- **Secure Webhook Registration**: Register and validate webhook endpoints with proper security measures

## Installation

1. Upload the `wp-marketing-plugin.zip` file to the `/wp-content/plugins/` directory
2. Extract the ZIP file
3. Activate the plugin through the WordPress admin interface
4. Configure the plugin settings

## Configuration

### Basic Configuration

1. Navigate to the plugin settings page
2. Configure user permissions and access levels
3. Set up default message templates and parameters

### n8n Integration Setup

1. Navigate to the Settings -> n8n Integration tab
2. Enter your n8n instance URL
3. Configure webhook endpoints and security tokens
4. Test the connection to ensure proper functionality

## Development

### Requirements

- PHP 7.4 or higher
- WordPress 5.0 or higher
- n8n instance (for full functionality)
- Evolution API (for WhatsApp messaging)

### Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

See [LICENSE](http://www.gnu.org/licenses/gpl-2.0.html) for more information.
