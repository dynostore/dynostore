sudo docker compose exec datacontainer2 python3 regist_on_metadata.py 322eda4b1d1da49d141ee00b8f1279f9b8af3f9266d27923fa8797fd64f75655 http://192.5.87.114:20001/ -m 100000000 -s 200000000000
sudo docker compose exec datacontainer2 python3 regist_on_metadata.py 322eda4b1d1da49d141ee00b8f1279f9b8af3f9266d27923fa8797fd64f75655 http://192.5.87.114:20002/ -m 100000000 -s 200000000000
sudo docker compose exec datacontainer3 python3 regist_on_metadata.py 322eda4b1d1da49d141ee00b8f1279f9b8af3f9266d27923fa8797fd64f75655 http://192.5.87.114:20003/ -m 100000000 -s 200000000000