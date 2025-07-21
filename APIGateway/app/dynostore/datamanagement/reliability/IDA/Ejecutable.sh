#!/bin/bash

#Version2 funciona en mi lap
echo  "N \t M \t ResponseTime";
for i in $(seq 1 31);do
        ./Dis $1 $2 $3 $4;
done
dis=" ";
for i in `seq 0 $2`
do
    dis=$dis"D$i "
done
echo  "\n";
echo "N \t M \t ResponseTime";
for i in $(seq 1 31);do
        ./Rec $5 $3 $dis;
done
