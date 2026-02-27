#
# This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
#
# (c) IT-Solutions4You s.r.o
#
# This file is licensed under the GNU AGPL v3 License.
# See LICENSE-AGPLv3.txt for more details.
#
#!/bin/bash

export DEFAULTCRM_ROOTDIR=$(cd -- "$(dirname "$0")" && pwd)
export USE_PHP=$(which php || echo "php")
cd "$DEFAULTCRM_ROOTDIR" || exit
$USE_PHP -f cron.php
