<!DOCTYPE html>
<head>
    <meta charset="utf-8"/>
    <title>meta-schema</title>
    <script src="node_modules/@json-editor/json-editor/dist/jsoneditor.js"></script>
    <link rel="stylesheet" id="theme-link" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" id="iconlib-link" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-md-6">
            <div class='json-editor-container'></div>
        </div>
        <div class="col-xs-12 col-md-6">
            <label class="sr-only" for="value">Value</label>
            <textarea class="form-control" id="value" rows="12" style="font-size: 12px; font-family: monospace;"></textarea>
        </div>
    </div>
</div>

<script>
const jsonEditorContainer = document.querySelector('.json-editor-container');
const value = document.querySelector('#value');
const editor = new JSONEditor(jsonEditorContainer, {
    ajax: true,
    schema: { "$ref": "schema/table.json" },
    theme: 'bootstrap4',
    show_errors: 'always',
    iconlib: 'fontawesome5',
    keep_oneof_values: false
});
editor.on('change', () => {
    value.value = JSON.stringify(editor.getValue(), null, 2)
})
</script>
</body>
</html>