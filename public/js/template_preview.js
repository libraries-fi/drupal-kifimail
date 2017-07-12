(function($, CodeMirror) {
  "use strict";

  function setupCodeEditor(textarea) {
    textarea.required = false;
    textarea.codeEditor = CodeMirror.fromTextArea(textarea, {
      mode: textarea.dataset.editorMode,
      tabSize: 2,
      lineNumbers: true
    });
    return textarea.codeEditor;
  }

  $('textarea.code-editor').each(function(i, element) {
    setupCodeEditor(element);
  });
}(jQuery, CodeMirror));
