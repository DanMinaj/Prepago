#!/bin/bash

killall screen

sudo screen -S  "PrepagoJavaScheduler" -dm bash -c "cd /root/david/java_code/M-Bus/ ; java -jar -Djava.library.path=. PrepagoJavaScheduler.jar; "

screen -S  "PrepagoDataLogger" -dm bash -c "cd /root/david/java_code/M-Bus/ ; java -jar -Djava.library.path=. /root/david/java_code/M-Bus/"PrepagoDataLogger".jar; "

screen -S  "ValveControl" -dm bash -c "cd /root/david/java_code/M-Bus/ ; java -jar -Djava.library.path=. ValveControl.jar; "

screen -S  "PrepagoAppRemote" -dm bash -c "cd /root/david/java_code/M-Bus/ ; java -jar -Djava.library.path=. /root/david/java_code/M-Bus/PrepagoRemoteControl.jar; "

screen -S  "smsQue" -dm bash -c "cd /root/david/java_code/Active_System/ ; java -Xmx42m -Xms2m -jar /root/david/java_code/Active_System/"smsQue".jar; "

screen -S  "vpnn" -dm bash -c "cd /root/.__DANIEL_SCRIPTS/ ; sudo openvpn --config Prepago_AidanONeill_Prepago.ovpn "

echo "Restarted servicess"