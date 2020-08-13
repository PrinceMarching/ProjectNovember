pod=`kubectl get pods | grep transformer | sed "s/ .*//"`

kubectl logs $pod user-container