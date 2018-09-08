<link rel="stylesheet" href="<?= plugin_dir_url( __FILE__ ) ?>styles.css">

<?php

function get_img_path($file_name, $src) {
    $base_dir = get_home_path();
    $path = substr($src, strpos($src, "//") + 2);
    $path = substr($path, strpos($path, "/") + 1);
    $path = $base_dir . $path;
    return $path;
}

?>

<div class="wrap">

    <div id="lio-page">

        <h1>Lighthouse Image Optimizer</h1>
        <h2>Optimize and replace bloated images</h2>

        <ol>
            <li>Run <a href="https://developers.google.com/web/tools/lighthouse/" target="__blank">Lighthouse</a> on the page of your website you want to optimize images. The easiest way  to do this is <a href="https://developers.google.com/web/tools/lighthouse/#devtools">using Chrome Developer Tools</a>, but you may also utilize the <a   href="https://developers.google.com/web/tools/lighthouse/#extension" target="__blank">Lighthouse Chrome Extension</a> or <a   href="https://developers.google.com/web/tools/lighthouse/#cli" target="__blank">run an audit on the command line using Node</a>.</li>
            <li><a href="https://developers.google.com/web/tools/lighthouse/#json" target="__blank">Download the report as a JSON file.</a></li>
            <li>Upload the JSON file below and click "Optimize Images."</li>
        </ol>

        <form enctype="multipart/form-data" action="" method="post">

            <input type="file" accept=".json" name="audit" />
            <?php submit_button("Optimize Images") ?>

        </form>

        <?php

            if (isset($_POST["submit"])) {

                $audit = $_FILES["audit"]["tmp_name"];
                $audit = file_get_contents($audit);
                $audit = json_decode($audit);
                $images = $audit->audits->{"uses-optimized-images"}->details->items;

                define('WEBSERVICE', 'http://api.resmush.it/ws.php?img=');

                $optimized_list = array();

                $failed_upload_count = 0;

                foreach ($images as $key => $image) {

                    $url = $image->url;
                    $query_string_pos = strrpos($url, "?");

                    if ($query_string_pos > 0) {
                        $url = substr($url, 0, $query_string_pos);
                    }

                    $optimized = json_decode(file_get_contents(WEBSERVICE . $url));

                    if (isset($optimized->error)) {
                        $failed_upload_count++;
                        if ($failed_upload_count == 1) {
                            echo "<h2>Failed uploads</h2>";
                        }
                        echo "Upload failed for " . $url . ": <b><i>" . $optimized->error_long . "</i></b>";
                    } else {
                        array_push($optimized_list, $optimized);
                    }

                }

                $successful_replacement_count = 0;

                foreach ($optimized_list as $new_img) {

                    $path = get_img_path($file_name, $new_img->src);

                    unlink($path);
                    $ch = curl_init($new_img->dest);
                    $fp = fopen($path, 'wb');
                    curl_setopt($ch, CURLOPT_FILE, $fp);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_exec($ch);

                    $result_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    if ($result_status == 200) {
                        $successful_replacement_count++;
                        if ($successful_replacement_count == 1) {
                            echo "<h2>Successfully compressed and replaced</h2>";
                        } else {
                            echo "<br>";
                        }
                        echo($path);
                    }

                    curl_close($ch);
                    fclose($fp);

                }

            }

        ?>

    </div>

</div>

<script src="<?= plugin_dir_url( __FILE__ ) ?>scripts.js"></script>