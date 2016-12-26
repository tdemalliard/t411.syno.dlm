# t411.syno.dlm
This GIT contains the files to build the dlm package for Synology Download Station.


This is a little tricky because of the way t411 api handle the authentification. So the dlm need a intermediary php script to get the torrent personalizedd with your account.

## Installation

### Make the dlm package
Clone the git, and do a tar gz archive with the INFO and search.php files:
```bash
git clone git@github.com:tdemalliard/t411.syno.dlm.git
tar zcf t411.syno.dlm INFO search.php
```

### add the dlm package
1. Go to download station > settings > BT search
2. Add the dlm package
3. Edit the new t411 line
4. Add your t411.io login and password. The verify button is no use. I did not code the account verification.

### Copy the torrent download script into the webserver
1. Active your NAS Web Station, using https.
2. Copy the file t411.syno.php to the root of your web folder.


## The documents supporting the developpement
https://global.download.synology.com/download/Document/DeveloperGuide/DLM_Guide.pdf

http://api.t411.io/