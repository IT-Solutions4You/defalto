#
# This file is part of Defalto – a CRM software developed by IT-Solutions4You s.r.o.
#
# (c) IT-Solutions4You s.r.o
#
# This file is licensed under the GNU AGPL v3 License.
# See LICENSE-AGPLv3.txt for more details.
#

export DEFALTOCRM_ROOTDIR=`dirname "$0"`
export USE_PHP=php

cd $DEFALTOCRM_ROOTDIR
# TO RUN ALL CORN JOBS
$USE_PHP -f cron.php
