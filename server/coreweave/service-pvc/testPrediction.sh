url=`./getServiceURL.sh`

keepGoing=1

while [ $keepGoing -gt 0 ]
do
	result=`curl -s -d '{"instances": ["That was easy"]}' $url`
	
	if [[ $result == *"predictions"* ]]; then
		echo $result
		keepGoing=0
	else
		echo "Timed out, trying again."
	fi
done
