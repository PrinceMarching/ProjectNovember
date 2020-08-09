url=`./getServiceURL.sh`

prompt="What follows is a conversation between a human and the world's most sophisticated artificial intelligence.  As you can see, the computer displays a shocking amount of cleverness and wit, as the discussion quickly becomes deep and philosophical.\n\nHuman: what\n\nComputer: "


keepGoing=1

while [ $keepGoing -gt 0 ]
do

	# break up this string to allow interp of $prompt
	# {"instances": ["$prompt"]}'
	curlDataA='{"instances": ["';
	curlDataB=$prompt;
	curlDataC='"]}';

	curlData="$curlDataA$curlDataB$curlDataC"

	echo -n "$curlData" > tempFile.txt 
	
	result=`curl -s --trace curlLog.txt -H "Content-Type: application/json" --data-binary '@tempFile.txt' $url`
	
	if [[ $result == *"predictions"* ]]; then
		echo $result
		keepGoing=0
	else
		echo "Timed out, trying again."
		echo $result
	fi
done
