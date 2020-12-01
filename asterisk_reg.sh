#!/bin/bash
pidof -o %PPID -x $0 >/dev/null && echo "ERROR: Script $0 already running" && exit 1

DB_USER="root"
DB_PASSWD=""
DB_NAME="pushdb"
DB_TABLE="pushdb_pushkeys"

#One of the users suggested using commented lines for better performance
#tail -Fn0 /var/log/asterisk/full | grep --line-buffered "Registered SIP" | \
#while read line ;

tail -Fn0 /var/log/asterisk/full | \
while read line ;
 do
        echo "$line" | grep "Registered SIP" > /dev/null
        if [ $? = 0 ]
        then



US=$(echo "${line}" | sed -e "s/.*Registered SIP '\(.*\)' at.*/\1/")

TK=$(asterisk -r -x "sip show peer $US" | sed -n "s/.*pn-prid=\(.*\):remote&.*/\1/p")


ACPTLEN=64
TKLENGTH=$(echo -n $TK | wc -m)

if [ "$TKLENGTH" = "$ACPTLEN" ]; then

DT=$(TZ=EEST date +'%F %T')
mysql --user=$DB_USER --password=$DB_PASSWD $DB_NAME << EOF
INSERT INTO $DB_TABLE (\`p_device\`, \`p_status\`, \`p_type\`, \`p_info\`, \`p_updated\`) VALUES ("$US", "1", "IOS", "$TK","$DT") ON DUPLICATE KEY UPDATE \`p_device\`=VALUES(\`p_device\`),\`p_status\`=VALUES(\`p_status\`),\`p_type\`=VALUES(\`p_type\`),\`p_info\`=VALUES(\`p_info\`),\`p_updated\`=VALUES(\`p_updated\`);




EOF

else
:


fi

fi

done
