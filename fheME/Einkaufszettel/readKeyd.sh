#! /bin/sh

### BEGIN INIT INFO
# Provides:             readKeyd
# Required-Start:       $syslog
# Required-Stop:        $syslog
# Default-Start:        2 3 4 5
# Default-Stop:         0 1 6
# Short-Description:    Remote barcode reader
### END INIT INFO

. /lib/lsb/init-functions

log_daemon_msg "Starting remote barcode reader" "readKeyd" || true
log_end_msg 0 || true

/home/pi/readKeyd /dev/input/event0 &

exit 0