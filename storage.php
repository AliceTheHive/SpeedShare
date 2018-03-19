<?php require_once './includes/secure_conn.php';
//Check if session has user, else quit to index.php
session_start();
if (!isset($_SESSION['username'])) {
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
if (isset($_SESSION['admin'])) {
    $url = 'index.php';
    echo '<meta http-equiv="refresh" content="0;url='. $url .'">';
    exit();
}
else {
	$username = $_SESSION['username'];
}
require './includes/header.php'; ?>
    <!-- Maxwell Crawford -->
    <br>
    <section>
        <h3>&#8226;&nbsp;Storage - Check out currently stored photos and grab their URLs.</h3>
    </section>
    <main>
    	<h4><em>Click on an image to view it separately in full-size.</em></h4>
    	<?php
    	//List images currently in user upload dir
        $dir = '../SS_uploads/'.$username;
        $dirimg = '/~mc1838/SS_uploads/'.$username;
    	$files = scandir($dir); //read into array

        //Check if there are files...
        if (!empty($files)){
            //Check if ONLY 'file' is '.'/'..' folderup spacer...
            if (!((count($files) == 2) and (substr($files[1], 0, 1) == '.'))){
                //Display table 1: img's as cropped thumbnails
                echo '<table class="urltable">';
                echo '<tr>';
                $maxrow = 3;
                $count = 0;
                foreach ($files as $img){
                    if (substr($img,0,1) != '.') { //ignore 'hidden' or 'folderup' files
                        //Get img px size
                        $imgsize  = getimagesize("$dir/$img");
                        $imgw = $imgsize[0];
                        $imgh = $imgsize[1];
                        $imgtype = $imgsize[2];

                        //Get dimensions for thumbnail (using 200 as base)
                        $finalw = 200;
                        $finalh = floor($imgh * ($finalw / $imgw));


                        //Get orig img resource (based on ext)
                        $imgorig = null;
                        if ($imgtype == 2){
                            $imgorig = imagecreatefromjpeg("$dir/$img");
                        }
                        elseif ($imgtype == 3){
                            $imgorig = imagecreatefrompng("$dir/$img");
                        }
                        elseif ($imgtype == 1){
                            $imgorig = imagecreatefromgif("$dir/$img");
                        }
                        elseif (($imgtype == 6) or ($imgtype == 15)){
                            $imgorig = imagecreatefromwbmp("$dir/$img");
                        }

                        //Create local thumbnail copy on server, with 'hidden' name
                        $dirimgcopylocal = $dir . '/.sm' . $img; //NOTE the dot!
                        $finalimg = imagecreatetruecolor($finalw, $finalh);
                        imagecopyresampled($finalimg, $imgorig, 0, 0, 0, 0, $finalw, $finalh, $imgw, $imgh);
                        imagejpeg($finalimg, $dirimgcopylocal);

                        //Get URL-safe name
                        $imgname = urlencode($img);

                        //Get actual URL:
                        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                        $url .= $_SERVER['SERVER_NAME'];
                        $url .= "$dirimg/$img";
                        $dirimgcopy  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                        $dirimgcopy .= $_SERVER['SERVER_NAME'];
                        $dirimgcopy .= $dirimg . '/.sm' . $img; //NOTE the dot!

                        //Output the thumbnail/link:
                        $imgtitle = strtok($imgname, '.');
                        echo "<td><a href=\"fullimage.php?image=" . $imgname .  '"><img class="userimg" src="' . "$dirimgcopy"   . '" alt="' . $imgname . '" title="' . $imgtitle . '" >' . ' </a></td>';

                        //Free resources used by imgs
                        imagedestroy($imgorig);
                        imagedestroy($finalimg);
                    }
                    if ($count > $maxrow){
                        $count = 0;
                        echo '</tr><tr>'; //reset and start new row!
                    }
                    else {
                        $count += 1;
                    }
                } //end loop 1
                $count = 0; //reset
                echo '</tr>';
                echo '</table><br>'; //end table1

                //Display table 2: image titles and URLs
                echo '<table class="urltable">'; //start table2
                echo '<tr>';
                echo '<th>Image Title</th>';
                echo '<th>URL</th>';
                echo '</tr><tr>';

                foreach ($files as $img) {
                    if (substr($img,0,1) != '.') {
                        //Get URL-safe name, title, and full URL
                        $imgname = urlencode($img);
                        $imgtitle = strtok($imgname, '.');
                        $url  = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';
                        $url .= $_SERVER['SERVER_NAME'];
                        $url .= "$dirimg/$img";

                        //Output data
                        echo '<td>' . $imgtitle . '</td>';
                        echo '<td><input size="100" type="text" value="' . $url . '"></td>';
                    }
                    echo '</tr><tr>'; //reset and start new row!
                } //end loop 2
                $count = 0; //reset
                echo '</tr>';
                echo '</table>'; //end table2
                echo '<br><br><br><br><br><br>'; //spacer
            } //end check for only folderup
            else {
                echo '<h4 style="text-align: center">No files uploaded yet!</h4>';
            }
        } //end empty files check
    	else {
            echo '<h4 style="text-align: center">No files uploaded yet!</h4>';
        }
    	?>
    </main>
<?php include './includes/footer.php'; ?>