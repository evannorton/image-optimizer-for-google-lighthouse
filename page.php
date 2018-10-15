<?php

    wp_enqueue_style("iofgl-styles", plugin_dir_url(__FILE__) . "styles.css");
    wp_enqueue_script("iofgl-scripts", plugin_dir_url(__FILE__) . "scripts.js");

    function iofgl_none_found()
    {
        echo "No unefficiently encoded images found.";
    }

    function iofgl_get_img_path($src)
    {
        $base_dir = get_home_path();
        $path = substr($src, strpos($src, "//") + 2);
        $path = substr($path, strpos($path, "/") + 1);
        $path = $base_dir . $path;
        return $path;
    }

    function iofgl_format_size($bytes)
    {
        if ($bytes < 1000) {
            $bytes = round($bytes, 2) . " B";
        } elseif ($bytes < 1000000) {
            $bytes = round(($bytes / 1000), 2) . " KB";
        } else {
            $bytes = round(($bytes / 1000000), 2) . " MB";
        }
        return $bytes;
    }

    function iofgl_handle_form()
    {
        if (isset($_POST["submit"])) {
            $audit = $_FILES["audit"]["tmp_name"];
            $audit = file_get_contents($audit);
            $audit = json_decode($audit);
            $images = $audit->audits->{"uses-optimized-images"}->details->items;

            if ($images) {

                $optimized_list = array();

                $failed_upload_count = 0;
                $cross_origin_count = 0;

                foreach ($images as $key => $image) {
                    if ($image->isCrossOrigin) {
                        $cross_origin_count++;
                        continue;
                    }

                    $url = $image->url;
                    $query_string_pos = strrpos($url, "?");

                    if ($query_string_pos > 0) {
                        $url = substr($url, 0, $query_string_pos);
                    }

                    $optimized = json_decode(wp_remote_get("http://api.resmush.it/ws.php?img=" . $url)["body"]);

                    if (isset($optimized->error)) {
                        $failed_upload_count++;
                        if ($failed_upload_count == 1) {
                            echo "<h2>Failed uploads</h2>";
                        } else {
                            echo "<br>";
                        }
                        echo "Upload failed for " . $url . ": <b><i>" . $optimized->error_long . "</i></b>";
                    } else {
                        array_push($optimized_list, $optimized);
                    }
                }

                if (count($images) == 0 || ($cross_origin_count == count($images))) {
                    iofgl_none_found();
                } else {
                    $successful_replacement_count = 0;

                    foreach ($optimized_list as $new_img) {
                        $path = iofgl_get_img_path($new_img->src);

                        if (!file_exists($path)) {
                            continue;
                        }

                        $original_size = $new_img->src_size;
                        $compressed_size = $new_img->dest_size;
                        $size_reduced = $original_size - $compressed_size;
                        $percent_reduced = round((1 - ($compressed_size / $original_size)) * 100, 2) . "%";
                        $original_size = iofgl_format_size($original_size);
                        $compressed_size = iofgl_format_size($compressed_size);

                        unlink($path);

                        file_put_contents($path, file_get_contents($new_img->dest));

                        $successful_replacement_count++;
                        if ($successful_replacement_count == 1) {
                            echo "<h2>Successfully compressed and replaced</h2>";
                        } else {
                            echo "<br>";
                        }
                        echo ($path . " - <b><i>" . $percent_reduced . " size reduction (" . $original_size . " -> " . $compressed_size . ")</i></b>");

                    }

                    if ($successful_replacement_count == 0 && $failed_upload_count == 0) {
                        iofgl_none_found();
                    }
                }
            } else {
                iofgl_none_found();
            }
        }
    }

?>

<div class="wrap">

    <div id="iofgl-page">

        <h1>Lighthouse Image Optimizer</h1>
        <h2>Optimize and replace bloated images</h2>

        <ol>
            <li>Run <a href="https://developers.google.com/web/tools/lighthouse/" target="__blank">Lighthouse</a> on
                the page of your website you want to optimize images. The easiest way to do this is <a href="https://developers.google.com/web/tools/lighthouse/#devtools">using
                    Chrome Developer Tools</a>, but you may also utilize the <a href="https://developers.google.com/web/tools/lighthouse/#extension"
                    target="__blank">Lighthouse Chrome Extension</a> or <a href="https://developers.google.com/web/tools/lighthouse/#cli"
                    target="__blank">run an audit on the command line using Node</a>.</li>
            <li><a href="https://developers.google.com/web/tools/lighthouse/#json" target="__blank">Download the report
                    as a JSON file.</a></li>
            <li>Upload the JSON file below and click "Optimize Images."</li>
        </ol>

        <p><b>NOTE: This plugin is not compatible with Wordpress sites running on localhost. Deploy to a staging environment before using Lighthouse Image Optimizer.</b></p>

        <form enctype="multipart/form-data" action="" method="post">

            <input type="file" accept=".json" name="audit" />
            <?php submit_button("Optimize images")?>

        </form>

        <?php
            iofgl_handle_form();
        ?>

    </div>

</div>