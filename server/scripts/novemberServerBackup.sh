passwordLineage="secret"

date=`date +"%Y_%b_%d_%a"`

if [ ! -d "~/backups" ]
then
	mkdir ~/backups
fi


mysqldump -u jcr13_pnServerU --password=$passwordLineage jcr13_pnServer > ~/backups/pnServer_$date.mysql
gzip -f ~/backups/pnServer_$date.mysql

# delete backup files older than two weeks
find ~/backups -mtime +14 -delete