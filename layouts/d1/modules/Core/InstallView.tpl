<div class="p-3 rounded bg-body m-3">
    <h1>{vtranslate('Installation wizard', $QUALIFIED_MODULE)}</h1>
    <div class="py-3">
        <a class="btn btn-primary me-2" href="index.php?module={$MODULE}&view=Install&mode=install">{vtranslate('Install extension', $QUALIFIED_MODULE)}</a>
        <a class="btn btn-primary me-2" href="index.php?module={$MODULE}&view=Install&mode=update">{vtranslate('Update extension', $QUALIFIED_MODULE)}</a>
        <a class="btn btn-success me-2" href="index.php?module={$MODULE}&view=Install&mode=migrate">{vtranslate('Migrate extension', $QUALIFIED_MODULE)}</a>
        <a class="btn btn-danger me-2" href="index.php?module={$MODULE}&view=Install&mode=delete">{vtranslate('Delete extension', $QUALIFIED_MODULE)}</a>
    </div>
</div>
