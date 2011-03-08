#!/bin/bash
#
# Cleans up old import files found in the /tmp directory.
#
find /tmp -type f -name ji_import_\* -mtime +7 | xargs rm