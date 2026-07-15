<?php
// This file injects a client-side script to convert most text inputs to Title Case
// It intentionally skips fields like username, email, phone, ids, and numeric fields.
?>
<script>
  // Client-side: title-case helper that preserves all-uppercase acronyms (e.g., "BSCS")
  function toTitleCase(str) {
    if (!str) return str;
    return str.split(/(\s+)/).map(function(part) {
      if (part.trim() === '') return part;
      if (/^[A-Z]{2,5}$/.test(part)) return part;
      return part.charAt(0).toUpperCase() + part.slice(1).toLowerCase();
    }).join('');
  }

  document.addEventListener('DOMContentLoaded', function() {
    var forms = document.querySelectorAll('form');
    forms.forEach(function(form) {
      var inputs = form.querySelectorAll('input[type="text"], input[type="tel"], input[type="search"], input:not([type])');
      inputs.forEach(function(input) {
        var name = (input.name || '').toLowerCase();
        var skip = /username|email|student_id|id|zip|phone|contact|batch|units|tuition|password|clientid|clientsecret/.test(name) || input.maxLength === 0;
        if (skip) return;

        input.addEventListener('blur', function(e) {
          e.target.value = toTitleCase(e.target.value.trim());
        });

        input.addEventListener('paste', function(e) {
          var clipboard = (e.clipboardData || window.clipboardData);
          var pasted = clipboard ? clipboard.getData('text') : '';
          if (!pasted) return;
          e.preventDefault();
          var transformed = toTitleCase(pasted.trim());
          var start = input.selectionStart || 0;
          var end = input.selectionEnd || 0;
          var val = input.value;
          input.value = val.slice(0, start) + transformed + val.slice(end);
          var pos = start + transformed.length;
          input.setSelectionRange(pos, pos);
        });
      });
    });
  });
</script>