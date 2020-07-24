shortURL=`kubectl get ksvc | grep "transformer" | sed -e "s/.*http/http/" | sed -e "s/ .*//"`

echo -n $shortURL
echo "/v1/models/gpt-pvc:predict"