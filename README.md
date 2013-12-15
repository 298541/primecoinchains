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


If you feel like donating:

            XPM: AccoQkyLNXYsheDiDy9Q5e6qv1VP4CN7ux
            PPC: PFTBKAPg3YwZRfWXZhJ6f7yXcS1V5Hcieq
            BTC: 12mJujuKGb3nxgySGgrH81HvjXH5ZDw1JU
