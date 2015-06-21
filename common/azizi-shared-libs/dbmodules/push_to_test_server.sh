#!/bin/bash
source=/www/common/dbmodules/
livepath=/var/www/html/common/dbmodules
livehost=akihara@192.168.5.40
user=apache
group=admin
echo "Pushing files to test server"
rsync -rvz -e ssh --progress --exclude=.git --exclude=nbproject --exclude=run.sh --exclude=.gitignore --delete $source $livehost:$livepath
ssh $livehost sudo /bin/chown -R $user:$group $livepath
#ssh $livehost sudo /usr/bin/find "$livepath" -type f -exec chmod 664 {} \\\;

ssh $livehost sudo /usr/bin/find "$livepath" -type f -exec chmod 664 {} \\\; -exec chown $user {} \\\; -exec chgrp $group {} \\\;
ssh $livehost sudo /usr/bin/find "$livepath" -type d -exec chmod 774 {} \\\; -exec chown $user {} \\\; -exec chgrp $group {} \\\;
ssh $livehost sudo /usr/bin/find "$livepath" -name \\\*.pl -exec chmod 764 {} \\\;
rm -rf $tmpdir$livepath
echo 'Changes pushed test server.'
