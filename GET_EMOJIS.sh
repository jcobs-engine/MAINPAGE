#!/bin/bash

function load()
{
    text=$1
    oft=$2
    i=0
    while [ "$i" -lt "$oft" ]; do
    echo -en "\015$text \033[31m[|]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[/]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[-]\033[0m"
    sleep 0.1
    echo -en "\015$text \033[31m[\]\033[0m"
    sleep 0.1
    i=$(( $i + 1 ))
    done
    sleep 0.1
    echo -en "\015$text \033[32m[done]\033[0m"
    echo ""
}

load "Starting Download Tool" 3

readarray -t emojis < <(awk -F\" 'NF>=3 {print $4}' github_emojis.json); for i in ${emojis[@]}; do wget -O $i ${i//DATA/'http://levi-jacobs.de'}; done
