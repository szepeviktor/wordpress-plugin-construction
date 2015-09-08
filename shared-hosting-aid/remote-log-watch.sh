#!/bin/bash
#
# Syncronize remote log file and email increments.
#
# DEPENDS        :apt-get install lftp logtail mailx
# CRON.D         :*/30 *	* * *	viktor	/usr/local/bin/remote-log-watch.sh

error() {
    echo "ERROR: $*" >&2
    exit $1
}

source "${HOME}/.remote-log-watch"

[ -z "$LOG_NAME" ] && error 10 "Missing log name."
[ -z "$LOCAL_LOGDIR" ] && error 11 "Missing local log dir."
[ -z "$LOGFILE" ] && error 12 "Missing log file name."
[ -z "$LFTP_CONNECT" ] && error 13 "Missing lftp connect string."
[ -z "$REMOTE_LOGDIR" ] && error 14 "Missing remote log dir."
#LFTP_SSL="set ssl:ca-file ${LOCAL_LOGDIR}/self-signed.pem;"
#LFTP_SSL="set ssl:verify-certificate off;"
#LFTP_SSL="set ftp:ssl-allow off;"
[ -z "$LFTP_SSL" ] && error 15 "Missing lftp SSL settings string."

[ -d "${LOCAL_LOGDIR}" ] || mkdir -p "${LOCAL_LOGDIR}"
[ -f "${LOCAL_LOGDIR}/${LOGFILE}" ] || touch "${LOCAL_LOGDIR}/${LOGFILE}"

# `mirror` leaves files on the same Inode
lftp -e "${LFTP_SSL} set xfer:clobber on; mirror -i ${LOGFILE} ${REMOTE_LOGDIR} ${LOCAL_LOGDIR}; bye" ${LFTP_CONNECT} \
    || error 1 "FTP error during ${LOG_NAME}: $?."

/usr/sbin/logtail -f "${LOCAL_LOGDIR}/${LOGFILE}" | mailx -E -s "[remote log watch] ${LOG_NAME}" viktor@szepe.net

# @TODO Loggly upload, only collect locally for daily 404 email
