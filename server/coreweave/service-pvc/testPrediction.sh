
keepGoing=1

while [ $keepGoing -gt 0 ]
do
	url=`./getServiceURL.sh`

	result=`curl -s -d '{"instances": ["That was easy"]}' $url`
	
	if [[ $result == *"predictions"* ]]; then
		echo $result
		keepGoing=0
	else
		echo "Timed out, trying again. ($url)"
	fi
done
