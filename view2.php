<?php
// Sanitize and validate file paths
function safePath($path) {
    $realBase = realpath('pcdo');
    $userPath = realpath($path);

    if ($userPath && strpos($userPath, $realBase) === 0) {
        return $userPath;
    }
    return false;
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_file'])) {
    $fileToDelete = safePath($_POST['delete_file']);
    if ($fileToDelete && file_exists($fileToDelete)) {
        unlink($fileToDelete);
        $message = "File deleted: " . basename($fileToDelete);
    } else {
        $message = "File not found or invalid path.";
    }
    header("Location: view2.php?msg=" . urlencode($message));
    exit;
}

// Get search input
$searchQuery = isset($_GET['search']) ? strtolower(trim($_GET['search'])) : '';
?>

<!DOCTYPE html>
<html>
<head>
    <title>View All Uploaded Files</title>
    <style>
        .coop-section {
            margin-bottom: 30px;
            border-bottom: 1px solid #ccc;
        }
        .file-entry {
            margin-left: 50px;
        }
        form {
            display: inline;
        }
        .delete-btn {
            color: red;
            border: none;
            background: none;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h2>All Uploaded Files</h2>

    <?php if (isset($_GET['msg'])): ?>
        <p style="color: green;"><?php echo htmlspecialchars($_GET['msg']); ?></p>
    <?php endif; ?>

    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="search" placeholder="Search Program or Cooperative Name" value="<?php echo htmlspecialchars($searchQuery); ?>">
        <button type="submit">Search</button>
        <a href="view2.php" style="margin-left:10px;">Reset</a>
    </form>

    <?php
    $uploadsDir = "pcdo";
    if (is_dir($uploadsDir)) {
        $programs = array_diff(scandir($uploadsDir), ['.', '..']);
        $matchesFound = false;

        foreach ($programs as $program) {
            $programPath = "$uploadsDir/$program";
            if (!is_dir($programPath)) continue;

            $coops = array_diff(scandir($programPath), ['.', '..']);

            foreach ($coops as $coop) {
                // Search filter — match program or coop
                if (
                    $searchQuery !== '' &&
                    strpos(strtolower($program), $searchQuery) === false &&
                    strpos(strtolower($coop), $searchQuery) === false
                ) {
                    continue;
                }

                $matchesFound = true;
                echo "<div class='coop-section'>";
                echo "<h3>Program: $program — Cooperative: $coop</h3>";

                $coopPath = "$programPath/$coop";
                if (is_dir($coopPath)) {
                    $areas = array_diff(scandir($coopPath), ['.', '..']);
                    foreach ($areas as $area) {
                        echo "<strong>$area</strong><br><br>";

                        $areaPath = "$coopPath/$area";
                        if (is_dir($areaPath)) {
                            $files = array_diff(scandir($areaPath), ['.', '..']);
                            foreach ($files as $file) {
                                $filePath = "$areaPath/$file";
                                echo "<div class='file-entry'>";
                                echo "<a href='$filePath' target='_blank'>$file</a>";

                                // Delete form
                                echo "<form method='POST' onsubmit=\"return confirm('Are you sure you want to delete this file?');\">";
                                echo "<input type='hidden' name='delete_file' value='" . htmlspecialchars($filePath, ENT_QUOTES) . "'>";
                                echo "<button type='submit' class='delete-btn'>[Delete]</button>";
                                echo "</form>";

                                echo "</div><br>";
                            }
                        }
                    }
                }

                echo "</div>";
            }
        }

        if (!$matchesFound) {
            echo "<p>No matching results found.</p>";
        }
    } else {
        echo "<p>No uploads found.</p>";
    }
    ?>

    <hr>
    <a href="index.php">Back to Upload Page</a>
</body>
</html>
