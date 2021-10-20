# ios-voip-push
## APN push notification services for IOS sip client with Asterisk or Freepbx or VitalPBX or any other Asterisk based IP PBX

### Background:

One may find creating a  push notifcation service  for general purpose applications is effortless. But configuring a functional push notification service for SIP client in conjunction with Asterisk based PBX system is not that straight forward.

After a long search for a usable push notification service for FreePbx all over internet and could not able to accumulate everything needed to make a fully functional Push Notification service, I decided to write simple scripts using shell and php 

### Prerequisites

1. [edamov/pushok](https://github.com/edamov/pushok)
This will connect to Apple APN servers and send notifications. Of course you can use any other library or you can even write your own scripts or simply use curl. Remember Apple needs a persistant connection to APNs servers, or it may flag your curl request as spam bots.

2. PHP, Mysql and your favourite shell
There is one [shell script](https://github.com/balusreekanth/ios-asterisk-push/blob/master/asterisk_reg.sh) in this repository to check asterisk logs for registartion requests and save the Registration contact information in database.
You can setup [monit](https://github.com/arnaudsj/monit) for this if you do not want to use this script. But the base idea is to monitor log files continuously  and extract apn tokens from softphone requests to save it in a separate database.

We are saving Registration Contact information in a sepearate database because peer information we obtain from `sip show peer` is not persistent.

To store push tokens we need to create a simple database something like below. You can change database columns their types and length as per your requirement.

Our interest is mainly in 2 columns  to store SIP user account and device token information. Of course you can store device type, or any other dynamic information you fetch from contact headers as per your requirements.

```sql
CREATE TABLE `pushdb_pushkeys` (
  `id` int(10) NOT NULL,
  `p_device` varchar(64) NOT NULL,
  `p_status` int(1) DEFAULT NULL,
  `p_type` varchar(10) DEFAULT NULL,
  `p_info` varchar(124) NOT NULL,
  `p_updated` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

3. [Asterisk](https://www.asterisk.org) or [Freepbx](https://www.freepbx.org) or [vitalpbx pbx](https://vitalpbx.org/en/) or [elastix pbx](https://www.elastix.org)

Once we get push tokens into database, we just modify extensions asterisk configuration to send push notification if the destination extension exist in database and it has push token.


### How to use: 

1. Make sure you have php, mysql and asterik system installed.
2. Clone this repository to `/var/lib/asterisk/pushscripts`  or any other directory of your choice but make sure scripts have executable permissions by asterisk. Please do not use your web dircetory as we are using php cli to run script from extensions So you can rename `push.php` to just `push`.
3. Install `asterisk_reg.sh` to `/usr/local/bin`
4. Change  database username, password, database name in scripts
5. Change key_id, team_id, app_bundle_id,and secrets in [push.php] (https://github.com/balusreekanth/ios-asterisk-push/blob/master/push.php)
6. Define key path `AuthKey_XXXXXX.p8`. you can obtain p8 key, teamid, keyid details from apple developer portal.copy p8 key in the directory which you defined in push.php
6. Move [push.service](https://github.com/balusreekanth/ios-asterisk-push/blob/master/push.service) to `/etc/systemd/system/`  in order to run the script automatically at boot. You can skip this step if you are using monit and cofnigured monit accordingly .

You can run these commands to enable and start the service:

```shell
systemctl daemon-reload
systemctl start push.service
systemctl status push.service
systemctl enable push.service
```

7. Now configure your extensions configuration file to check if the destination extension exist in our database. If it exists, we do send call to push context else we process call normally.

The extensions configuration file may look different from system to system, depending on your asterisk distro. Below a sample configuration is given for reference.

```
same  => n,GoSubIf($["${pushneed}" = "${CALL_DESTINATION}"]?push,s,1(${CURRENT_DEVICE}))

[push]
exten => s,1,NoOP(SUB: Send push notification)
same => n,Set(MAX_TRIES=15)
same => n,Set(TRY=1)
;send args to push script
same => n,System(/var/lib/asterisk/pushscripts/push "${CALL_DESTINATION}" "${CALLERID(num)}" "${CALLERID(name)}" )
```

### Enjoy  push notifications for SIP calls on IOS  and save your battery
In fact, on newer IOS versions, there is no alternative to receive incoming calls when app is in background than using push service!

# Need help?
Write your comments and issues in issues section of this repository. Or you can mail at balusreekanthATgmailDOTcom

# Would you like to improve this?
I would love to see pull requests to further improve this script. 


## Donate
If this project help you reduce time to develop, you can buy me a cup of coffee. :)

[![paypal](https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=99YKLH5LPK5YA)
