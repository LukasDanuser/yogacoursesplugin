# CoursesPlugin

This plugin only works with WooCommerce

To make this plugin work you also need following plugins:
"Insert PHP Code Snippet" by xyzscripts.com
"Thanks Redirect for WooCommerce" by Nitin Prakash
"WooCommerce" by Automattic
"WooCommerce Payments" by Automattic
"UsersWP" by AyeCode

In "Thanks Redirect for WooCommerce" you need to set the thanks redirect to:
"<< yourURL >>/thanks"

In "UsersWP" go to:
settings>>Form Builder>>Form Options: set "Registration Action" to "Auto approve + Auto Login" AND "Redirect Page" to "Checkout"

With this plugin you can sell 2 memberships (half year or full year).
You also can create and sell online courses.

## Contact
Main contact: l.danuser@rafisa.ch
second contact: s.kuhn@rafisa.ch

## TODO
- Integrate generated ZOOM links into subscriber emails
- Implement under- and overmeetings
- refactoring the Code
- clean the database
- test the new cost system
- WordPress database prefix is used to identify WordPress tables from others. As of now all tables use this prefix removing the purpuse of the prefix. This was done by mistake, so we need to remove the prefix from non-WordPress tables. https://themeisle.com/blog/wordpress-database-prefix/
