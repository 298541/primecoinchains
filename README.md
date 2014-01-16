primecoinchains
===============

Updates and lists all chains of the primecoin network, similar to a block explorer

SETUP:

* Install latest primecoin client.
* Install some webserver with mysql and php (lampp for example)
* Throw all the scripts and folder in your public web server directory
* Set up lib/conf.php to your preferences
* Add cronjob ($ crontab -e) to run getblocks.php and getinfo.php every minute via php

getinfo.php gets latest network information, while getblocks.php loads blocks into your database.

Then, wait for the blockchain to sync and move over to the database.

ABOUT PRIMECHAINER
Primechainer is the tool to calculate the full prime chain to further work with it in the PHP scripts.
Primechainer source can be found in lib/primechainer_sourcecode. (Sorry, no makefile yet!)
primechainer.exe was compiled by me under Linux x64. Compile it yourself for MAX SECURITY!

If you feel like donating:

            XPM: TUoCLdyDNmXgKFgiQgkxJhL8rq6mixvX9R
            PPC: PFTBKAPg3YwZRfWXZhJ6f7yXcS1V5Hcieq
            BTC: 1383CyywThWrGvyUwdPxPan4R1QMrg4cJF
			Thank you!