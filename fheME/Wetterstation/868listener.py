import subprocess
#import json
#import datetime
#import MySQLdb
#import time
import os

dir = os.path.dirname(os.path.realpath(__file__))

from threading import Thread

# sending to api
def sendToApi(text):
    #data = json.loads(text)

    subprocess.run("/usr/bin/php "+dir+"/update.php '"+text+"'", shell=True)

    #db = MySQLdb.connect(host="localhost",    # your host, usually localhost
    #    user="fheme",         # your username
    #    passwd="YvPJMjuehPVhqKBA",  # your password
    #    db="fheme")        # name of the data base

    #cur = db.cursor()

    #cur.execute(f"""UPDATE `Wetterstation` SET WetterstationOutdoorTemp = '{data['temperature_C']}', WetterstationOutdoorHumidity = '{data['humidity']}', WetterstationLastUpdate = UNIX_TIMESTAMP() WHERE WetterstationID = '1'""")

    #db.commit()

    #db.close()


# This method creates a subprocess with subprocess.Popen and takes a List<str> as command
def execute(cmd):
    popen = subprocess.Popen(cmd, stdout=subprocess.PIPE, bufsize=1, universal_newlines=True)
    for stdout_line in iter(popen.stdout.readline, ""):
        yield stdout_line 
    popen.stdout.close()
    return_code = popen.wait()
    if return_code:
        raise subprocess.CalledProcessError(return_code, cmd)

for line in execute(['/home/nemiah/rtl_433/build/src/rtl_433', '-f','868.000M', '-F', 'json', '-R', '78']):
    thread = Thread(target = sendToApi, args = (line,))
    thread.start()
