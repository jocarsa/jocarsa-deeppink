<?php
session_start();

// --- Logout Process ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['loggedin']);
    header("Location: index.php");
    exit;
}

// --- Login Process ---
if (!isset($_SESSION['loggedin'])) {
    if (isset($_POST['login'])) {
        $username = trim($_POST['username']);
        $password = trim($_POST['password']);
        // Default credentials: username "jocarsa" and password "jocarsa"
        if ($username === 'jocarsa' && $password === 'jocarsa') {
            $_SESSION['loggedin'] = true;
            header("Location: index.php");
            exit;
        } else {
            $login_error = "Invalid credentials!";
        }
    }
    // Show the login form if not logged in
    ?>
    <!doctype html>
    <html>
    <head>
      <meta charset="utf-8">
      <title>Login - DeepPink Dashboard</title>
      <link rel="stylesheet" href="css/style.css">
      <style>
        /* Simple login form styling */
        .login-container {
          max-width: 400px;
          margin: 100px auto;
          padding: 20px;
          background: #fff;
          border: 1px solid #ddd;
          box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .login-container h2 {
          text-align: center;
          margin-bottom: 20px;
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
          width: 100%;
          padding: 10px;
          margin: 10px 0;
          border: 1px solid #ccc;
          border-radius: 4px;
        }
        .login-container input[type="submit"] {
          width: 100%;
          padding: 10px;
          background: #0073aa;
          color: #fff;
          border: none;
          border-radius: 4px;
          cursor: pointer;
        }
        .login-container input[type="submit"]:hover {
          background: #005177;
        }
        .login-error {
          color: red;
          text-align: center;
        }
      </style>
    </head>
    <body>
      <div class="login-container">
        <h2>Login</h2>
        <?php if(isset($login_error)) { echo "<p class='login-error'>$login_error</p>"; } ?>
        <form method="post" action="index.php">
          <input type="text" name="username" placeholder="Username" required>
          <input type="password" name="password" placeholder="Password" required>
          <input type="submit" name="login" value="Login">
        </form>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// --- Database Connection & Initialization ---
try {
    $db = new PDO('sqlite:db.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
$db->exec("CREATE TABLE IF NOT EXISTS reports (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    url TEXT NOT NULL,
    report_html TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)");

// --- Process Report Deletion ---
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $db->prepare("DELETE FROM reports WHERE id = :id");
    $stmt->execute([':id' => $id]);
    header("Location: index.php?tab=view_reports");
    exit;
}

// --- Process Report Generation & Saving ---
if (isset($_POST['dp_action']) && $_POST['dp_action'] === 'generate_report') {
    require_once 'DeepPink.php';
    $url = trim($_POST['url']);
    $web = new DeepPink($url);
    ob_start();
    echo "<table class='report-table'>";
      $web->dameTitulo();
      $web->dameDescripcion();
      $web->damePalabras();
      $web->nubeDePalabras();
      for ($i = 1; $i <= 6; $i++) {
          $web->dameTitulos($i);
      }
    echo "</table>";
    $report_html = ob_get_clean();

    if (isset($_POST['dp_save']) && $_POST['dp_save'] === '1') {
        $stmt = $db->prepare("INSERT INTO reports (url, report_html) VALUES (:url, :report_html)");
        $stmt->execute([':url' => $url, ':report_html' => $report_html]);
        $save_message = "Report saved successfully.";
    }
}

// --- Retrieve Saved Reports ---
$reports = $db->query("SELECT * FROM reports ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// --- Determine Active Tab (default to "new_report") ---
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'new_report';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>DeepPink Dashboard</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Dashboard Layout Styles */
    body {
      font-family: sans-serif;
      background-color: #f1f1f1;
      margin: 0;
      padding: 0;
    }
    #dashboard-header {
      background: #23282d;
      color: #fff;
      padding: 10px 20px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    #dashboard-header h1 {
      margin: 0;
      font-size: 24px;
    }
    #dashboard-header p {
      margin: 0;
      font-size: 14px;
    }
    #dashboard-header a {
      color: #fff;
      text-decoration: none;
      margin-left: 20px;
    }
    #dashboard-container {
      display: flex;
      min-height: calc(100vh - 60px);
    }
    #dashboard-nav {
      width: 200px;
      background: #32373c;
      color: #fff;
      padding: 20px;
    }
    #dashboard-nav ul {
      list-style: none;
      padding: 0;
    }
    #dashboard-nav ul li {
      margin-bottom: 10px;
    }
    #dashboard-nav ul li a {
      color: #fff;
      text-decoration: none;
    }
    #dashboard-content {
      flex: 1;
      padding: 20px;
      background: #fff;
    }
    .report-table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    .report-table th, .report-table td {
      border: 1px solid #ddd;
      padding: 8px;
    }
  </style>
</head>
<body>
  <!-- Top Header -->
  <div id="dashboard-header">
    <div>
      <h1>Corporate Information</h1>
      <p>DeepPink Dashboard – Manage Your Reports</p>
    </div>
    <div>
      <a href="index.php?action=logout">Logout</a>
    </div>
  </div>

  <!-- Main Dashboard Container -->
  <div id="dashboard-container">
    <!-- Navigation Pane -->
    <div id="dashboard-nav">
      <ul>
        <li><a href="index.php?tab=new_report">Create New Report</a></li>
        <li><a href="index.php?tab=view_reports">View Reports</a></li>
      </ul>
    </div>

    <!-- Content Area -->
    <div id="dashboard-content">
      <?php
      // Display save message if available
      if (isset($save_message)) {
          echo "<p style='color:green;'>$save_message</p>";
      }
      // If a generated report is waiting to be saved, show it
      if (isset($report_html) && (!isset($_POST['dp_save']) || $_POST['dp_save'] !== '1')) {
          echo "<h2>Generated Report</h2>";
          echo $report_html;
          echo "<form method='post' action=''>";
          echo "<input type='hidden' name='dp_action' value='generate_report'>";
          echo "<input type='hidden' name='url' value='" . htmlspecialchars($url, ENT_QUOTES) . "'>";
          echo "<input type='hidden' name='dp_save' value='1'>";
          echo "<input type='submit' value='Save Report'>";
          echo "</form>";
      }
      // New Report Tab
      if ($tab === 'new_report') {
      ?>
          <h2>Create New Report</h2>
          <form method="post" action="">
            <p>
              <label for="url">Enter URL to analyze:</label><br>
              <input type="url" id="url" name="url" required style="width:100%;max-width:400px;">
            </p>
            <p>
              <input type="hidden" name="dp_action" value="generate_report">
              <input type="submit" value="Generate Report">
            </p>
          </form>
      <?php
      }
      // View Reports Tab
      elseif ($tab === 'view_reports') {
      ?>
          <h2>Saved Reports</h2>
          <?php if (count($reports) > 0) { ?>
          <table class="report-table">
            <thead>
              <tr>
                <th>Date</th>
                <th>URL</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reports as $r) { ?>
                <tr>
                  <td><?php echo htmlspecialchars($r['created_at']); ?></td>
                  <td><?php echo htmlspecialchars($r['url']); ?></td>
                  <td>
                    <a href="index.php?action=view&id=<?php echo $r['id']; ?>&tab=view_reports">View</a> |
                    <a href="index.php?action=delete&id=<?php echo $r['id']; ?>&tab=view_reports" onclick="return confirm('Are you sure you want to delete this report?');">Delete</a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
          <?php } else {
              echo "<p>No reports saved yet.</p>";
          }
      }
      // View a single report
      if (isset($_GET['action']) && $_GET['action'] === 'view' && isset($_GET['id'])) {
          $id = intval($_GET['id']);
          $stmt = $db->prepare("SELECT * FROM reports WHERE id = :id");
          $stmt->execute([':id' => $id]);
          $report = $stmt->fetch(PDO::FETCH_ASSOC);
          if ($report) {
              echo "<h2>View Report</h2>";
              echo "<p><a href='index.php?tab=view_reports'>&laquo; Back to Reports</a></p>";
              echo $report['report_html'];
          }
      }
      ?>
    </div>
  </div>
</body>
</html>

