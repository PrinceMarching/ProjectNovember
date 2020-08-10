podName=`kubectl get pods | grep "tensorflow" | sed "s/ .*//"`


extIP=`kubectl get service | grep "tensorflow" | sed "s/[a-z\-]* *[a-z]* *[0-9.]* *//i" | sed "s/ .*//"`


url=`kubectl logs tensorflow-jupyter-749fd4bfc-25h2d | grep "127.0.0.1:8888" | head -n 1 | sed "s/.*http/http/" | sed "s/127.0.0.1/$extIP/"`

echo $url