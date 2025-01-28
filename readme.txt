=== Mail Merger by SourceCode ===
Contributors: sourcecodeplugins
Tags: email, mail merge, csv upload, wordpress cron
Requires at least: 5.0
Tested up to: 6.7.1
Stable tag: 1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Send batch emails using CSV data with placeholders, HTML templates, and WordPress Cron for automated scheduling.

## Description

Mail Merger by SourceCode is a comprehensive WordPress plugin designed to streamline your email campaigns. It enables you to send batch emails efficiently by using recipient data from uploaded CSV files. With support for dynamic placeholders, you can personalise each email to include information like the recipient's name or email address. The plugin also provides robust support for HTML email templates, allowing you to craft visually appealing messages that align with your branding. Additionally, Mail Merger offers advanced settings for email headers, including BCC functionality, ensuring better control over email delivery. The entire process is automated using WordPress Cron, making it ideal for scheduling emails in manageable batches without requiring manual intervention. Whether you’re managing newsletters, event invitations, or follow-ups, Mail Merger by SourceCode provides the tools you need for a smooth and effective email campaign.

## Features
- Upload CSV files to send personalised emails in batches.
- Use placeholders like `{name}` for dynamic email content.
- Supports HTML email templates.
- Schedule batch email processing using WordPress Cron.
- Includes BCC email support for all outgoing emails.
- Validates and sanitizes all uploaded CSV data.
- Supports both comma and semicolon-separated CSV files.

## Installation
1. Download the plugin ZIP file.
2. Go to the WordPress Admin Dashboard.
3. Navigate to `Plugins > Add New` and click `Upload Plugin`.
4. Select the plugin ZIP file and click `Install Now`.
5. Activate the plugin from the `Plugins` page.

## Usage
1. Go to the `Mail Merger` menu in the WordPress admin dashboard.
2. Enter your email subject and body, using placeholders like `{name}` for dynamic content - Note: placeholders are for BODY only.
3. Upload a CSV file containing columns for `First name` and `Email`.
4. Click `Send Emails` to schedule the emails.

## Frequently Asked Questions

### Q: What CSV format is supported?
A: The plugin supports both comma-separated and semicolon-separated CSV files. Ensure your file includes headers like `First name` and `Email`.

### Q: How do I include a BCC email address?
A: You can set a BCC email address in the plugin's settings page. It will be applied to all outgoing emails.

### Q: How does the email scheduling work?
A: Emails are sent in batches using WordPress Cron. You can configure the batch size and interval in the code. Default is 50 emails every 5 minutes.

### Q: Can I use HTML in the email body?
A: Yes, the plugin supports basic HTML tags like `<a>`, `<p>`, `<br>`, `<strong>`, `<em>`, and more. For security, all HTML is sanitized.

### Q: What happens if the file upload fails?
A: The plugin validates uploaded files and displays error messages if the upload fails. Ensure the file is a valid CSV and meets the required format.

## Changelog

### Version 1.0
- Initial release.
- Added support for CSV uploads and batch email sending.
- Integrated WordPress Cron for scheduling.
- Basic HTML template support.

## License
This plugin is licensed under the GPLv2 or later. See the [GNU General Public License](https://www.gnu.org/licenses/gpl-2.0.html) for details.

## Support
For support, visit [SourceCode Plugins](https://sourcecode.es) or email us at `hello@sourcecode.es`.
