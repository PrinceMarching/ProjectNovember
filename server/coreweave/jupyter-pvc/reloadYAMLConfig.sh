kubectl apply -f model-storage-pvc.yaml
kubectl apply -f jupyter-pvc.yaml
kubectl apply -f tensorflow-deployment.yaml
kubectl apply -f tensorflow-service.yaml
