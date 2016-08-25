Watchful.li sitelist
====================

With this script you can display an overview of all your Watchful.li sites and export the data to Excel.
For easy browsing and searching in the site list the jQuery DataTables plugin was added.

To get this working you need a Watchful.li API key that you can get through your Watchful.li profile.
Install the API key on line 10 of sitelist.php.

##Installation instructions:

* Create a new folder "sitelist" in the root of your website on your webserver.  This can be on a local development environment too, as long as it has Internet access.

* Upload all files to the newly created folder "sitelist" on your webserver.

* Add your Watchful.li API key in the index.php file (line 10).

* Go to your website: http://www.your-website.com/sitelist/

* Optionally protect access to the sitelist folder through password protection with .htaccess. See also: https://davidwalsh.name/password-protect-directory-using-htaccess

##Show only published sites or all sites?

On line 15 a constant (SHOW_ONLY_PUBLISHED) is setup to show either only the published sites, or all sites.
If you want to show only the published sites, this line should look like this:

```php
define('SHOW_ONLY_PUBLISHED', true);
```

If you want to show all sites, this line should look like this:

```php
define('SHOW_ONLY_PUBLISHED', false);
```

##Changelog:

25-aug-2016:
* Added much faster toggle between display of sites with updates and all sites
* Added column toggler to show or hide columns.
* Added savestate to DataTables.
* Dropped Foundation version.
* Added a popup to the website URL showing frontend and administrator links.
* Added option to show only sites with updates.
* Changed refresh button to All sites.
* Added button to show only sites with updates.
* Updated Watchful.li API url.

19-aug-2016:
* Added option to show only the published sites, or all sites.

18-aug-2015:
* Added a column to show the number of updates available and added a refresh button.
* Added site statistics: nr. of websites, nr. of sites with an update and nr. of updates available.
* Added Foundation 5 version.
* Added website up/down status.

16-aug-2016:
* Integrated jQuery DataTables plugin to allow better display and searching.

12-aug-2016:
* Initial version.
