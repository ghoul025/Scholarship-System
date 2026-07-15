<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register — Scholarship System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <style>
      .tab-active { background-color: #1d4ed8; color: #fff; }
      .iframe-panel { width: 100%; border: none; }
      /* small nice shadow */
      .panel { box-shadow: 0 6px 18px rgba(13, 42, 148, 0.08); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-6">
  <div class="max-w-4xl w-full bg-white rounded-xl panel overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white p-4 flex items-center justify-between">
      <div class="flex items-center gap-3">
        <img src="pictures/ICC_New-Logo_2022.jpg" alt="logo" class="w-10 h-10 rounded-full bg-white p-1">
        <div>
          <div class="font-bold">Scholarship System</div>
          <div class="text-sm opacity-80">Register / Apply</div>
        </div>
      </div>
      <div>
        <a href="index.php" class="bg-white text-blue-600 px-3 py-1 rounded-full hover:bg-gray-100">Back to Login</a>
      </div>
    </div>
    <div class="p-4">
      <div class="flex gap-2 mb-4">
        <button id="tab-user" class="px-4 py-2 rounded-full border border-blue-200 tab-active">Create Account</button>
        <button id="tab-tes" class="px-4 py-2 rounded-full border border-blue-200">TES Application</button>
      </div>

      <div id="panels" class="space-y-4">
        <div id="panel-user">
          <iframe id="iframe-user" class="iframe-panel" src="register.php" title="Create Account" onload="resizeIframe(this)"></iframe>
        </div>
        <div id="panel-tes" style="display:none;">
          <iframe id="iframe-tes" class="iframe-panel" src="tes_registration.php" title="TES Application" onload="resizeIframe(this)"></iframe>
        </div>
      </div>
    </div>
  </div>

  <script>
    const tabUser = document.getElementById('tab-user');
    const tabTes = document.getElementById('tab-tes');
    const panelUser = document.getElementById('panel-user');
    const panelTes = document.getElementById('panel-tes');
    const iframeUser = document.getElementById('iframe-user');
    const iframeTes = document.getElementById('iframe-tes');

    function selectTab(tab) {
      if (tab === 'user') {
        tabUser.classList.add('tab-active');
        tabTes.classList.remove('tab-active');
        panelUser.style.display = '';
        panelTes.style.display = 'none';
      } else {
        tabTes.classList.add('tab-active');
        tabUser.classList.remove('tab-active');
        panelTes.style.display = '';
        panelUser.style.display = 'none';
      }
      // try resizing visible iframe
      setTimeout(() => {
        const visible = tab === 'user' ? iframeUser : iframeTes;
        if (visible && visible.contentWindow) {
          resizeIframe(visible);
        }
      }, 60);
    }

    tabUser.addEventListener('click', () => selectTab('user'));
    tabTes.addEventListener('click', () => selectTab('tes'));

    function resizeIframe(iframe) {
      try {
        const doc = iframe.contentDocument || iframe.contentWindow.document;
        // set min height to 680 for comfortable space
        const height = Math.max(doc.body.scrollHeight, doc.documentElement.scrollHeight, 680);
        iframe.style.height = height + 'px';
      } catch (e) {
        // cross-origin or error: fallback height
        iframe.style.height = '800px';
      }
    }

    // initial selection
    selectTab('user');
  </script>
</body>
</html>
