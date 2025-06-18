<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Bajaj Camp Bypass 1 Rs</title>
  <style>
    body {
      background: #111;
      color: #0f0;
      font-family: monospace;
      margin: 0;
      padding: 0;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
    }
    .container {
      width: 100%;
      max-width: 500px;
      padding: 20px;
      box-sizing: border-box;
    }
    h2 {
      text-align: center;
      color: #0f0;
      margin-top: 30px;
    }
    form {
      display: flex;
      flex-direction: column;
      gap: 10px;
      background: #222;
      padding: 20px;
      border: 1px solid #0f0;
      border-radius: 8px;
    }
    input[type="text"], button {
      padding: 10px;
      font-size: 16px;
      background: #333;
      color: #0f0;
      border: 1px solid #0f0;
      border-radius: 4px;
    }
    button {
      cursor: pointer;
      background: #0f0;
      color: #111;
      font-weight: bold;
    }
    .log {
      margin-top: 20px;
      white-space: pre-wrap;
      background: #222;
      padding: 10px;
      border: 1px solid #0f0;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h2>💸 Per Refer 1Rs Booster</h2>
    <form method="POST">
      <label>Enter UPI ID:</label>
      <input type="text" name="upi" placeholder="example@upi" required>
      <button type="submit">Submit & Trace</button>
    </form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['upi'])) {
    function traceRedirects($url, $headers) {
        $redirects = [];
        $eventLinks = [];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_USERAGENT => $headers['User-Agent'],
        ]);
        $response = curl_exec($ch);
        $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);

        preg_match_all('/Location:\s?(.*)/i', $response, $matches);
        foreach ($matches[1] as $loc) {
            $redirectUrl = trim($loc);
            $redirects[] = $redirectUrl;

            if (strpos($redirectUrl, "adjust.com") !== false) {
                $parsed = parse_url($redirectUrl);
                parse_str($parsed['query'], $query);
                foreach ($query as $key => $value) {
                    if (strpos($key, 'event_callback_') === 0) {
                        $eventLinks[] = urldecode($value);
                    }
                }
            }
        }
        return [$redirects, $finalUrl, $eventLinks];
    }

    function fireEventLinks($links, $headers) {
        $results = [];
        foreach ($links as $link) {
            $ch = curl_init($link);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => $headers['User-Agent'],
                CURLOPT_TIMEOUT => 10,
            ]);
            curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $results[] = ['url' => $link, 'status' => $code];
            curl_close($ch);
        }
        return $results;
    }

    $upi = trim($_POST['upi']);
    $refer = "Hv0gl";
    $p3 = "Ol9qE4";
    $user_encoded = urlencode($upi);

    $baseUrl = "https://clickmudra.com/o/action.php";
    $finalUrl = "https://clickmudra.com/c?cid=11&uid=1&p1={$user_encoded}&p2={$refer}&p3={$p3}&p4=&p5=&p6=&type=offer";

    $headers = [
        'User-Agent' => 'Mozilla/5.0',
        'Content-Type' => 'application/x-www-form-urlencoded',
    ];

    echo "<div class='log'>[i] Submitting UPI: <b>$upi</b>\n";

    $postData = http_build_query([
        'user' => $upi,
        'refer' => $refer,
        'field1' => '',
        'field2' => '',
        'field3' => '',
    ]);

    $ch = curl_init($baseUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    $json = json_decode($resp, true);

    if ($json && $json['status'] == 'success') {
        echo "[✓] Submission Successful: " . $json['message'] . "\n";
        echo "[→] Final redirect URL:\n$finalUrl\n";

        list($redirects, $dest, $callbacks) = traceRedirects($finalUrl, $headers);
        echo "[i] Redirect Chain:\n";
        foreach ($redirects as $i => $url) {
            echo " → Redirect " . ($i + 1) . ": $url\n";
        }

        echo "[✓] Final Destination: $dest\n";

        if (!empty($callbacks)) {
            echo "[i] Found Adjust Callback URLs:\n";
            foreach ($callbacks as $link) {
                echo " → $link\n";
            }

            echo "[i] Firing callback postbacks...\n";
            $results = fireEventLinks($callbacks, $headers);
            foreach ($results as $res) {
                echo "[✓] {$res['url']} → Status: {$res['status']}\n";
            }
        } else {
            echo "[x] No event_callback postbacks found.\n";
        }
    } else {
        echo "[x] Submission failed: " . ($json['message'] ?? 'Unknown error') . "\n";
    }

    echo "</div>";
}
?>
  </div>
</body>
</html>