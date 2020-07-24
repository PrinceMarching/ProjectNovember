url=`./getServiceURL.sh`

curl -d '{"instances": ["That was easy"]}' $url
