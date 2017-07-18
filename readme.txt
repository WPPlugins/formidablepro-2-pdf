=== Formidable PRO2PDF ===
Contributors: rasmarcus, aizatmustafin
Donate link: http://formidablepro2pdf.com/
Tags: pro2pdf, pdf, generation, pdftk, formidable, forms, create
Requires at least: 3.0.1
Tested up to: 4.7
Stable tag: 2.65
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Map web forms to PDF forms then with one simple shortcode - display a link on any post,  page, form, or view the merged PDF on a PC or mobile device.

== Description ==

Formidable Form add-on plugin to map Formidable Form fields to PDF form fields. Then - with one simple shortcode - display a download link or button on any post, page, form, or view to the filled-in PDF document on your web user's PC or mobile device.

Features:

= FREE VERSION =

* Create Webform to PDF form Field Maps
* Shortcode to Fill and Download PDFs
* Import/Export Pre-Made Templates
* Includes Complete Working Demo
* [Free Templates](http://formidablepro2pdf.com/templates) on Plugin Site
* Automatic Downloads
* Flatten PDF Form


= CONTRIBUTE VERSION =

* Email PDF as Attachment
* Map Two Datasets to One PDF
* Password Protect PDF File
* Format PDF Fields
* Export to .docx file
* Works with all Formidable Field Types
* Works with Formidable Signature Addon
* Works with Formidable Repeatable Sections
* Works with Formidable Embedded Forms
* Unlimited Forms/Sites Available



Visit the [Formidable PRO2PDF website](http://www.formidablepro2pdf.com/) to compare versions, review documentation, and for support.

[youtube http://www.youtube.com/watch?v=zOA-rGyv-js]

== Installation ==

This section describes how to install the plugin and get it working.

= From your WordPress dashboard = 
1. Visit 'Plugins → Add New'
2. Search for 'Formidable PRO2PDF'
3. Activate Formidable PRO2PDF from your Plugins page.


= From WordPress.org = 

1. Download FormidablePRO2PDF.
2. Upload the 'formidablepro-2-pdf' directory to your `/wp-content/plugins/` directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate Formidable PRO2PDF from your Plugins page.

= Once Activated = 

1. Make sure that you have at least one Formidable form, **and at least one form entry** in the form you want to merge into a pdf.
2. Choose "New Field Map" in "Field Map to use".
3. Click "Upload a PDF file" in "Field Map Designer" section.
4. Choose new layout in "Layout to use".
5. Add field mappings to your PDF file in "Manage your custom layout here" section.
6. Copy/paste the shortcode or press "Export" button to download merged PDFs.

= Demo Installation Video =
[youtube http://www.youtube.com/watch?v=zOA-rGyv-js]

NOTE: The plugin uses [PDFTK](https://www.pdflabs.com/docs/pdftk-man-page/) to fill in PDF files. This means that your server has to have PHP shell commands enabled. If your server does not have `pdftk` installed, you can still use the plugin to generate 1 PDF file for 1 Formidable form.

If you [purchase](http://formidablepro2pdf.com/) the plugin using [our website](http://formidablepro2pdf.com/), you do not need to install `pdftk` or to enable shell commands on your server. You'll use our API according to the [Terms of Service](http://formidablepro2pdf.com/terms-of-service/). 

Enter the activation code on the plugin options page.

== Frequently Asked Questions ==

= What are the requirements? =

[Formidable Forms plugin](http://www.formidablepro.com), make sure that your server can execute shell commands, and that `pdftk` is installed on your server. 

You'll also need to have PHP `MB` or `iconv` extensions installed. They are usually installed on web servers.

If you purchase the plugin, no additional software installation is need.

PHP version should be at least 5.3.

= Does the plugin create PDF files? =

Not at this time. Currently the plugin populates pre-made PDF form fields with mapped data from Formidable Form and FormidablePro form fields.

Future plans include adding HTML to PDF capabilities.

= Is support offered for the free version? =

Yes - standard user support is available through the support forum or purchase a key code for premium level support.

= Does the plugin work with multisite installations? =

Yes, the plugin works with WordPress Multisite. Site limits still apply.

== Screenshots ==

1. One Simple Shortcode.
2. Map Formidable Form Fields to PDF Text Form Fields.
3. Export Dynamic PDFs in Wordpress.
4. Activatation Page.
5. Settings Page.
6. Templates Page.

== Changelog ==
= 2.65 =
Dynamic password fix

= 2.64 =
Dynamic attachment name/password fix when form using IDs

= 2.63 =
Dynamic password

= 2.62 =
Option "Do not store entries" - empty data pdf fix

= 2.61 =
Dynamic email attachment name (by Field ID)

= 2.60 =
č unicode fix

= 2.59 =
Uninstall db fix

= 2.58 =
Update version control

= 2.57 =
Backup restore entries fix

= 2.56 =
/tmp folder check

= 2.55 =
Formidable check fix

= 2.54 =
Select field text fix

= 2.53 =
Restore backup fix

= 2.52 =
PDF file name fix

= 2.51 =
UTF-8 fix

= 2.50 =
Bugfix for encoding data

= 2.49 =
Bugfix for import field map

= 2.48 =
Bugfix for show active websites

= 2.47 =
Bugfix for format for same line [line1] [line2]

= 2.46 =
Bugfix for field names in PDF

= 2.45 =
Delete 'Pdf' attachments after sending mail

= 2.44 =
Add link in admin under ACTIVATED FORMS tab

= 2.43 =
'Unicode' language support

= 2.42 =
Show only current site url in admin 

= 2.41 =

'Upload' folders permission issue.

= 2.40 =

'Carriage return to comma' and  shortcode empty issue

= 2.39 =

Bugfix for empty short code issue.

= 2.38 =

Bugfix for slahes issue.

= 2.37 =

Bugfix for compatipility with some other plugins.

= 2.35 =

Allow editors to manage fieldsets. 

= 2.34 =

Optimize getting the dataset field options.

= 2.33 =

Bugfix for signature.

= 2.32 =

Bugfix for the Image URL field.

= 2.31 =

Bugfix for temp directory detection.
Bugfix for signature.
Temp directory cleaning optimization.

= 2.30 =

Bugfix for disabled mbstring.

= 2.29 =

Multiple checkboxes bugfix.

= 2.28 =

Add Address and Credit Card fields.
Ampersand bugfix after Formidable Forms update.

= 2.27 =

Prevent unnecessary error messages in the PHP error log.
Bugfix for version notification.

= 2.26 =

Bugfix after MySQL functions update.

= 2.25 =

Bugfix after MySQL functions update.

= 2.24 =

Compatibility with MariaDB and PHP7.
Removed mysql_* functions.

= 2.23 =

Check if mysql_connect() function exists, and show an error message if it doesn't.

= 2.22 =

Compatibility with WP 4.5

= 2.21 =

Fixing Failed to load PDF issues.

= 2.20 =

Multiple checkboxes bugfix for separate repeatable fields.

= 2.19 =

Field keys in repeatable fields.

= 2.18 =

Bugfix for automatic field map creation.

= 2.17 =

Additional option for restricting user downloads by role or user ID.

= 2.16 =

Improved "role" shortcode parameter.

= 2.15 =

Improved "role" shortcode parameter.

= 2.14 =

New shortcode parameters (role, user). Improved formatting options.

= 2.13 =

Improved repeatable fields.

= 2.12 =

Bugfix for PDF filename in Firefox.

= 2.11 =

Image alignment options.

= 2.10 =

Image alignment options.

= 2.9 =

Updated Demo. Added automatic mapping for new field maps.

= 2.8 =

Uninstall hooks to delete all plugin data, when plugin is uninstalled.

= 2.7 =

Bugfix for checkboxes/radio labels.

= 2.6 =

Bugfix for checkboxes/radio labels

= 2.5 =

Additional date formats.

= 2.4 =

Additional date formats.

= 2.3 =

Bugfix for offline sites.

= 2.2 =

Bugfix for Capitalize ALL formatting option.

= 2.1 =

Bugfix for field names (IP, item key, ...).

= 2.0 =

Replaced field IDs with field keys in field map designer.
Field map designer: save button will automatically select saved field map.
Bugfix for generated PDF hook.
Bugfix for multiple PDF attachments.

= 1.7.39 =

Improvements for slow servers; options for offline websites.

= 1.7.38 =

Only compatible field maps for forms can be selected.

= 1.7.37 =

Licence key won't be removed automatically when the key expires.

= 1.7.36 =

Removed missing repeatble fields shortcodes.

= 1.7.35 =

Secure download links.

= 1.7.34 =

Plugin hook for saving PDFs.

= 1.7.33 =

Images in PDFs.

= 1.7.32 =

Date for Formidable updated at / created at fields is now in local time. Image uploads in PDF files.

= 1.7.31 =

Additional counters.

= 1.7.30 =

Additional Webform Data Field ID.

= 1.7.29 =

Additional formatting options that can be changed using a plugin.

= 1.7.28 =

Additional formatting option for numbers.

= 1.7.27 =

Additional formatting option to capitalize all text.

= 1.7.26 =

Additional formatting option for dates.

= 1.7.25 =

Additional formatting option for numbers.

= 1.7.24 =

Fix multiple checkbox fields for non-premium users.

= 1.7.23 =

Fix multiple checkbox fields for non-premium users.

= 1.7.22 =

Additional formatting options in repeatable fields.

= 1.7.21 =

Date formatting in repeatable fields.

= 1.7.20 =

TypeIn signatures support.

= 1.7.19 =

Fixed bug with saving field maps. Added automatic backups for your field maps.

= 1.7.18 =

Fixed bug with quotes in PDF field names.

= 1.7.17 =

Added a new field where you can change PDF file name in e-mails. Fixed issue with special characters in PDF field names.

= 1.7.16 =

Added a new field where you can change PDF file name in e-mails. Fixed issue with special characters in PDF field names.

= 1.7.15 =

Bugfixes, support for embedded form IDs.

= 1.7.14 =

Bugfixes.

= 1.7.13 =

New formatting option, bugfixes.

= 1.7.12 =

Added HTML fields, and a formatting option that removes empty lines in text field value.

= 1.7.11 =

Added warning for old Formidable plugin versions.

= 1.7.10 =

Temporary folder bugfix for some servers.

= 1.7.9 =

Bugfix for special UTF-8 characters.

= 1.7.8 =

New formatting option. Debug info for clients.

= 1.7.7 =

PDFaid bugfixes.

= 1.7.6 =

Added conversion to DOCX using PDFaid.

= 1.7.5 =

Bugfixes.

= 1.7.4 =

Added new flatten option to transform text into images. No one will be able to copy-paste text from your PDFs.

= 1.7.3 =

Made field map names required field.

= 1.7.2 =

Added settings page. Field previews won't slow down field map designer anymore (unless enabled in settings).

= 1.7.1 =

Bugfix for checkboxes.

= 1.7.0 =

Compatibility with WP 4.3. Possibility to select which e-mail notifications should have PDF attachments. New shortcode parameter label=0 that outputs only URL of the PDF file.

= 1.6.0.17 =

Bugfix

= 1.6.0.16 =

Compatibility with WordPress 4.3. Added formatting option for free version.

= 1.6.0.15 =

Better support for multiple checkboxes.

= 1.6.0.14 =

Added alert for PHP version, bugfixes.

= 1.6.0.13 =

Added support for dynamic Formidable fields.

= 1.6.0.12 =

Bugfix for CSS conflict with Fusion Builder.

= 1.6.0.11 =

Added new formatting options for dates. Removed extra lines in multiline text fields on some servers.

= 1.6.0.10 =

Add repeatable sections support.

= 1.6.0.9 =

Fix bug when saving layout.

= 1.6.0.8 =

Missing files bugfix.

= 1.6.0.7 =

Corrected encoding issues, added password protection, added button to duplicate layouts.

= 1.6.0.6 =

Removed PHP warnings, sorted WebForm Field IDs, removed unused WebForm Fields.

= 1.6.0.5 =

Updated plugin URL.

= 1.6.0.4 =

Signature plugin.

= 1.6.0.3 =

Bugfixing, email attachments.

= 1.6.0.2 =

Removed default form files, Tested compatibility with WordPress 4.2.2

= 1.6.0.1 =

Tested compatibility with WordPress 4.2.1 and Formidable Forms 2.0.04

= 1.6 =

Added shortcodes, export into PDF, API interface, Fields Map Designer.

== Upgrade Notice ==

= 1.6 =

First version of the plugin with basic functionality.
