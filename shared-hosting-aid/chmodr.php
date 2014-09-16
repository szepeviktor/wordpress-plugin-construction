<pre><?php

// uncomment it!
exit;


// a specific directory
//chmod_R('./dir', 0777, 0777);

// and all directories from the current down
$xdh = opendir('.');
while (($xfile = readdir($xdh)) !== false) {
    if ($xfile != '.' && $xfile != '..') {
        $xfullpath = $xfile;
        // file and dir permissions
        chmod_R($xfullpath, 0664, 0775);
    }
}
closedir($dh);

function chmod_R($path, $filemode, $dirmode) {

    if (is_dir($path) ) {
        if (!chmod($path, $dirmode)) {
            $dirmode_str=decoct($dirmode);
            print "Failed applying filemode '$dirmode_str' on directory '$path'\n";
            print "  `-> the directory '$path' will be skipped from recursive chmod\n";
            return;
        }
        print "$path - OK\n";

        $dh = opendir($path);
        while (($file = readdir($dh)) !== false) {
            // skip self and parent pointing directories
            if ($file != '.' && $file != '..') {
                $fullpath = $path.'/'.$file;
                chmod_R($fullpath, $filemode,$dirmode);
            }
        }
        closedir($dh);

    } else {
        if (is_link($path)) {
            print "link '$path' is skipped\n";
            return;
        }
        if (!chmod($path, $filemode)) {
            $filemode_str=decoct($filemode);
            print "Failed applying filemode '$filemode_str' on file '$path'\n";
            return;
        }
        print "$path - OK\n";
    }
}

?></pre>
