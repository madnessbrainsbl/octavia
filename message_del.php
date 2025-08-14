<?php

$del = filter_input(INPUT_GET, 'del');
    if ($del="message_del"){
        exec("sudo rm /projects/sys/cache_bot/*");
        }
