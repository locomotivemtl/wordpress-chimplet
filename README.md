# Chimplet

[![devDependency Status](https://david-dm.org/locomotivemtl/wordpress-chimplet/dev-status.svg)](https://david-dm.org/locomotivemtl/wordpress-chimplet#info=devDependencies) [![Build Status](https://travis-ci.org/locomotivemtl/wordpress-chimplet.png?branch=develop)](https://travis-ci.org/locomotivemtl/wordpress-chimplet)

WordPress Plugin that automatically synchronizes Users, Taxonomies, Terms, and Posts to MailChimp Lists, Groupings, Groups, and Campaigns.

**Contributors:** [locomotive](https://github.com/locomotivemtl), [mcaskill](https://github.com/mcaskill), [jonathanbardo](https://github.com/jonathanbardo)

**Requires at least:** WordPress 4, PHP 5.4, MailChimp API Key

**Tested up to:** WordPress 4.2

**Stable tag:** trunk (master)

**License:** [MIT](http://en.wikipedia.org/wiki/MIT_License)

## Description

Chimplet does two things:

1. Chimplet can synchronize WordPress users to a MailChimp List and, using third-party support, assign Terms as Interest Groupings to each Subscriber.
2. Chimplet will create a unique RSS feed for each subset of distinct Terms, from the collection of selected Terms.
2. Chimplet can automatically generate RSS-driven Campaigns based each subset of distinct Terms (the selcted Interests from Subscribers).


## Installation

1. Download a copy of the highest-numbered tag _or_ of the 'master' branch.
2. Upload 'chimplet' to the '/wp-content/plugins/' directory.
3. Activate the plugin through the "Plugins" menu in WordPress.
4. Click on the new menu item "Chimplet".
5. Set up Chimplet with your MailChimp account and synchronize your users and generate your campaigns.
6. Read the documentation below for more information.

## Usage

Settings for the plugin can be found under the "Chimplet" menu item in the WordPress administration sidebar.

The plugin's settings are divided into three sections:

1. **API Management** — For enabling the plugin's features with a MailChimp API key.
2. **List Management** — For selecting a mailing List to use, which post-related Terms to use as Groups, which users to sync based on their role, enabling/disabling one-way user synchronization, and synchronizing WordPress users to MailChimp.
3. **Campaign Management** — For providing a master schedule, enabling/disabling campaign automation, and generating RSS-driven campaigns.

### Step 1

After activating Chimplet or if its settings have not been saved, the settings page will only display the "API Management" panel. Chimplet requires an API key from MailChimp in order to execute its features. The panel features instructions on how to acquire and generate an API key.

Once the API Key is integrated with Chimplet, you will be provided with additional options.

Note: Removing the API Key will disable Chimplet’s data synchronization features and no longer provide it access to your account to manage your subscribers and generated campaigns. Disabling Chimplet does not delete any data from MailChimp nor does it disable Post Category feeds and the active RSS-Driven Campaigns.

### Step 2

With an integrated API Key, the settings page will now display an additional panel: "List Management". Chimplet will list all existing mailing Lists from your MailChimp account with brief details pertaining to each one. Chimplet does not provide an option to create a new List; to create a new List, visit your MailChimp account.

After selecting an existing List for Chimplet to work with, you will be presented with additional settings to manage the selected List:

* **Select Taxonomy Terms** — Select one or more Terms, across available Taxonomies, to be added—accordingly—as Groups and Interest Groupings. A label will dynamically indicate the number of Groups selected and the number of Segments each combination of one or more Terms will create. Each Segment is a Campaign.
* **Select User Roles** — Select one or more roles to filter WordPress users to synchronize (if enabled) to MailChimp.
* **Subscribers** — Enable/disable automatic user synchronization, and trigger a batch synchronization. A label will indicate the number of users that are syncable based on selected user roles (requires saving changes to user roles).

Chimplet does not provide a UI for a WordPress user's profile page to select their interests. This is outside of the plugin's scope. Actions and filters are provided for third-parties to inform Chimplet of a user's interests which are then synchronized to MailChimp.

When selecting only a few Terms, you will notice the "Segments" counter will be low. When selecting many Terms, the counter will be very high. From the selected Terms, Chimplet will create a collection of Segments. Each Segment represents a subset of one or more distinct Terms — each Segment represents a Subscriber's selection of Interests.

The "Automate subscribers synchronization" checkbox enables two things:

* Individual user synchronization: any time a user makes a change to their WordPress profile, Chimplet will sync changes to their equivalent MailChimp subscription. Chimplet will even synchronize new registrations.
* Batch user synchronization: a "Synchronize Subscribers" button will allow you to trigger a batch synchronization of all eligible WordPress users to MailChimp. This option can be used at any time to make sure everyone is up to date.

### Step 3

After configuring your Groups and synchronizing your WordPress users, the settings page will now display an additional panel: "Campaign Management". By default, it isn't enabled.

Enabling the "Automate creation of Campaigns" checkbox will provide additional options:

* **Synchronize Campaigns** — A button to trigger the deletion of existing Chimplet-created Campaigns and the generation of new Campaigns based on synchronized Terms, available recipients based on Segments, and last-saved Scheduling and RSS Template.
* **Schedule** — A collection of options to configure the timing for all RSS-driven Campaigns generated by Chimplet.
* **RSS Template** — A drop-down list to select an existing Template; to create a new Template, visit your MailChimp account.

Each Campaign is assigned to a unique List Segment and its equivalent RSS feed. Each Segment/RSS represents the aggregated selection of a subscriber's Interests.

Examples:

> Subscribers that selected only the "Foo" category will only be assigned to that Campaign.

> Subscribers that selected the "Foo" and "Baz" categories will only be assigned to the "Foo, Baz" Campaign.

If a Subscriber changes their selected Interests, Chimplet will move the Subscriber to the Segment representing their new Interests.

The more Terms you make available to Users to select, the more Campaigns Chimplet will have to generate. Chimplet creates one Campaign for each combination of Interest(s) possible.

> If Subscribers can only select from 4 Categories, Chimplet can create up to 15 Campaigns. If you offer 8 Categories, that's up to 255 Campaigns.

If there are no Subscribers assigned to a particular Segment, for example no one selected the "Qux" category: that Chimplet will skip creating that Campaign. MailChimp won't allow you to create Campaigns with empty Segments.

It's a good practice to re-generate your Campaigns before the send out date arrives in order to make sure all your Subscribers are properly assigned.

## Contribution & Support

Through [GitHub](https://github.com/locomotivemtl/wordpress-chimplet/issues).

## Changelog

### 1.0.0

Initial release
