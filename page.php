<link rel="stylesheet" href="<?= plugin_dir_url( __FILE__ ) ?>styles.css">

<div class="wrap">

    <h1>Lighthouse Image Optimizer</h1>
    <h2>Optimize and replace bloated images:</h2>
    <ol>
        <li>Run <a href="https://developers.google.com/web/tools/lighthouse/" target="__blank">Lighthouse</a> on the page of your website you want to optimize images. The easiest way to do this is <a href="https://developers.google.com/web/tools/lighthouse/#devtools">using Chrome Developer Tools</a>, but you may also utilize the <a href="https://developers.google.com/web/tools/lighthouse/#extension" target="__blank">Lighthouse Chrome Extension</a> or <a href="https://developers.google.com/web/tools/lighthouse/#cli" target="__blank">run an audit on the command line using Node</a>.</li>
        <li><a href="https://developers.google.com/web/tools/lighthouse/#json" target="__blank">Download the report as a JSON file.</a></li>
        <li>Upload the JSON file below and click "Optimize Images."</li>
    </ol>

    <form>
        <input type='file' name='audit' />
        <?php submit_button("Optimize Images") ?>
    </form>

</div>

<script src="<?= plugin_dir_url( __FILE__ ) ?>scripts.js"></script>