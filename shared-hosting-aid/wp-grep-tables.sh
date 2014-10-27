#!/bin/bash
#
# Extract WordPress tables from a one-database dump.
# Usage: mysql-grep-table.sh "db-dump.sql" [<DB_PREFIX>] > "only-wp-tables.sql"
#
# VERSION       :0.1
# DATE          :2014-10-26
# AUTHOR        :Viktor Szépe <viktor@szepe.net>
# LICENSE       :The MIT License (MIT)
# URL           :https://github.com/szepeviktor/debian-server-tools
# BASH-VERSION  :4.2+


#FIXME: handle top and bottom comment blocks /*!40101 SET */"
#       sed -n '/^\/\*![0-9]\{5\} SET /p'


# Find a section delimited by "--"
Grep_mysql_section() {
    local SECTION="$1"

    sed -n '/^-- '"$SECTION"'$/{N; :a;N; /\n--\($\| Dump completed\)/{p;d}; ba}'
}

# Find all four sections
Dump_table() {
    local FILE="$1"
    local TABLE="$2"

    Grep_mysql_section "Table structure for table \`${TABLE}\`" < "$FILE"
    Grep_mysql_section "Dumping data for table \`${TABLE}\`" < "$FILE"
    Grep_mysql_section "Indexes for table \`${TABLE}\`" < "$FILE"
    Grep_mysql_section "AUTO_INCREMENT for table \`${TABLE}\`" < "$FILE"
}

# Find table data in WP DB Migrate output
Wpdbmigrate_table() {
    local FILE="$1"
    local TABLE="$2"

    sed -n '/^# Table: `'"$TABLE"'`$/{:a;N; /\n# End of data contents of table `'"$TABLE"'`$/{N;p;d}; ba}' < "$FILE"
}

# Detect dump type
Grep_table() {
    local FILE="$1"
    local TABLE="$2"
    local FIRST_LINE

    FIRST_LINE="$(head -n 1 "$FILE")"

    # mysqldump
    # phpMyAdmin
    if grep -q -- "^-- MySQL dump" <<< "$FIRST_LINE" \
        || grep -q -- "^-- phpMyAdmin SQL Dump" <<< "$FIRST_LINE"; then
        Dump_table "$FILE" "$TABLE"

    # WP DB Migrate plugin
    elif grep -q -- "^# WordPress MySQL database migration" <<< "$FIRST_LINE"; then
        Wpdbmigrate_table "$FILE" "$TABLE"

    # unknown
    else
        echo "Unknown dump type." >&2
        exit 2
    fi
}

##############

DB_DUMP="$1"
if ! [ -f "$DB_DUMP" ]; then
    echo "Dump file does not exist." >&2
    exit 1
fi

DB_PREFIX="wp_"
[ -z "$2" ] || DB_PREFIX="$2"

#TODO: multisite tables
for T in commentmeta comments links options postmeta posts \
    terms term_relationships term_taxonomy usermeta users; do

    Grep_table "$DB_DUMP" "${DB_PREFIX}${T}"
done
