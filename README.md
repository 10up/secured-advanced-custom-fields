# Advanced Custom Fields v4.0 Beta

Welcome to the beta repository for Advanced Custom Fields v4.0.
This repository will be temporarily available for beta testing, issue reporting and bug fixing. Once the v4.0 plugin is ready, this repository will be removed and all code will be available on the WordPress.org website as per usual.


-----------------------

### Overview

Since the recent change of WP's terms and conditions regarding hosting of plugins, the ACF plugin has undergone some BIG changes to adhere to the new rules.
The problem is that ACF includes the 'Premium' code inside the free hosted plugin. All 'Add-on' code has been taken out of the core plugin, and replaced with seperate plugins for each of the 4 add-ons.

During this proccess, the entire plugin has been re written to use actions and filters instead of nested class structures. Hopefuly, as a result, we will see some Performance increases from both PHP and SQL!


### Where are the Add-ons?

The add-ons are currently not available for download, however, over the next few days a new page will appear on the ACF website where you can enter your activation code / login to your account and download the add-on files.

Each add-on is now a plugin which will pull updates from the ACF website. Each plugin has also been writen to allow for theme inclusion! Meaning, you don't need to install the plugin, you can simply include it in your functions.php file. Doing this, however, will prevent any plugin updates.


### Participate in Testing

If you have the time, please participate in this beta testing. The more developers we can get, the quicker this new version can be released!
Please report all issues related to this beta version here on github, not on the ACF support forum.


### New Featues
* [Optimized] Optimize performance by removing heavy class structure and implementing light weight hooks & filters!
* [Fixed] Fix issue with Preview / Draft where preview would not save custom field data - http://support.advancedcustomfields.com/discussion/4401/cannot-preview-or-schedule-content-to-be-published
* [Changed] Remove all Add-on code from the core plugin and separate into individual plugins with self hosted updates
* [Added] Add field group title validation
* [Fixed] Fix WPML issue where get_field_object / get_field find the wrong field
* [Fixed] Fix duplicate functionality - http://support.advancedcustomfields.com/discussion/4471/duplicate-fields-in-admin-doesn039t-replicate-repeater-fields 
* [Added] Add conditional statements to tab field - http://support.advancedcustomfields.com/discussion/4674/conditional-tabs
* [Improved] Improved creating / registering a custom field - Need to ad documnetation - For now, please look at the core/fields/dummy.php file and see how a field is made!
* [Added] Add new Hooks / Filter - Need to add documnetation


### Download Add-Ons
You can now download your purchased Add-ons here: http://www.advancedcustomfields.com/add-ons-download/


### Known Issues
* The register_field() function has been removed as there is a new way to create / regsiter your own field type. This means that all previously submitted add-ons will not work. I will help all developers re-make their add-ons for v4.
* No add-ons are included in the plugin - these will be released seperatly soon
* Export page is not yet available


### Things to be improved
* Caching - Please feel free to offer any advice for caching in the plugin


### Thank You
A BIG THANK YOU to everyone who can help assist in this new version!