pod=`kubectl get pods | grep predictor | sed "s/ .*//"`

kubectl logs $pod kfserving-container