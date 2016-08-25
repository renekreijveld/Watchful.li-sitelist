Watchful.li sitelist
====================

With this script you can display an overview of all your Watchful.li sites and export the data to Excel.
For easy browsing and searching in the site list the jQuery DataTables plugin was added.

To get this working you need a Watchful.li API key that you can get through your Watchful.li profile.
Install the API key on line 10 of sitelist.php.

##Installation instructions (Bootstrap 3 version):

* Upload all files to a folder "sitelist" on your webserver. This can be on a local development environment too, as long as it is connect to the Internet.

* Rename sitelist-bootstrap3.php to sitelist.php.

* Delete file sitelist-foundation5.php.

* Add your Watchful.li API key in the sitelist.php file (line 10).

* Go to your website: http://www.your-website.com/sitelist/sitelist.php

##Installation instructions (Foundation 5 version):

* Upload all files to a folder "sitelist" on your webserver. This can be on a local development environment too, as long as it is connect to the Internet.

* Rename sitelist-foundation5.php to sitelist.php.

* Delete file sitelist-bootstrap3.php.

* Add your Watchful.li API key in the sitelist.php file (line 10).

* Go to your website: http://www.your-website.com/sitelist/sitelist.php

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
