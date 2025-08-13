<?php
//we need
/*

- coop need to enter first therye name 
- then add evertything in the checklist of PCDO
- make the checklist of PCDO a lnk to that folder for the coop to upload their files in
- make a folder to everyhting in the checklist with each coop as the name of the folder as the name of their cooperative
- make a search bar for just the name of cooperative in folders
- make that its able to see if there is a file in a specific folder then will be marked as incomplete or complete
- make it that in every folder that is under the folder of a specific coop each folder must just have one file in it

*/
?>

<?php
// Define checklist items (required folders)
$checklistItems = [
    "Letter",
    "MCDC Endorsement",
    "Project proposal",
    "Financial Plan",
    "GA Resolution_ Avail",
    "GA Resolution 25percent",
    "Board Resolution Signatories",
    "BOD Resolution ExOfficio",
    "Certified Members List",
    "Secretary Certificate",
    "Disclosure_Statement",
    "Sworn Affidavit",
    "Past Projects",
    "Surety Bond",
    "CDA Reregistration Certificate",
    "Certificate of Compliance",
    "Bio Data",
    "Photocopy of 2 Valid Id",
    "Photocopy of BIR official receipt",
    "Audited F or S for last 3 years and latest CAPR",
    "Authenticated copy of Articles and ByLaws of Cooperative",
    "LGU or SP Accreditation",
    "MAO Certificate",
    "MDRRMO Certification"
];

// Helper function: sanitize coop name
function cleanCoopName($name) {
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $name);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload File</title>
    <style>
        .upload-link {
            display: block;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <h2>Cooperative Document Upload</h2>
    <form action="index.php" method="GET">
        <label>Enter Cooperative Name:</label>
        <input type="text" name="coop" required value="<?php echo isset($_GET['coop']) ? htmlspecialchars($_GET['coop']) : ''; ?>">
        <button type="submit">Go</button>
    </form>

    <?php
    // If coop is entered, show checklist folders
    if (isset($_GET['coop'])):
        $coopName = cleanCoopName($_GET['coop']);
        $baseDir = "uploads/" . $coopName;

        // Create coop base folder if not exists
        if (!is_dir($baseDir)) {
            mkdir($baseDir, 0755, true);
        }

        echo "<h3>Checklist for <em>$coopName</em></h3>";

        // List checklist folders with upload forms
        foreach ($checklistItems as $item):
            $folder = $baseDir . "/" . $item;
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }

            // Check if folder has a file
            $files = array_diff(scandir($folder), ['.', '..']);
            $status = (count($files) === 0) ? "Incomplete" : "Complete";

            echo "<div style='margin-bottom:15px;'>";
            echo "<strong>$item</strong> - <span style='color:" . ($status === "Complete" ? "green" : "red") . ";'>$status</span>";

            if ($status === "Incomplete"):
            ?>
                <form action="index.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="coop" value="<?php echo htmlspecialchars($coopName); ?>">
                    <input type="hidden" name="area" value="<?php echo htmlspecialchars($item); ?>">
                    <input type="file" name="uploaded_file" required>
                    <button type="submit" name="upload">Upload</button>
                </form>
            <?php
            else:
                // Show existing file(s)
                foreach ($files as $f) {
                    echo "<div><a href='$folder/$f' target='_blank'>$f</a></div>";
                }

                // Allow re-upload (will replace old file)
                ?>
                <form action="index.php" method="POST" enctype="multipart/form-data" style="margin-top:5px;">
                    <input type="hidden" name="coop" value="<?php echo htmlspecialchars($coopName); ?>">
                    <input type="hidden" name="area" value="<?php echo htmlspecialchars($item); ?>">
                    <input type="file" name="uploaded_file" required>
                    <button type="submit" name="upload">Re-upload (replace)</button>
                </form>
                <?php
            endif;

            echo "</div>";
        endforeach;
    endif;

    // Handle file upload
    if (isset($_POST['upload'])) {
        $coopName = cleanCoopName($_POST['coop']);
        $area = $_POST['area'];
        $targetDir = "uploads/" . $coopName . "/" . $area . "/";

        // Delete existing file if any (to allow only one)
        $existingFiles = array_diff(scandir($targetDir), ['.', '..']);
        foreach ($existingFiles as $file) {
            unlink($targetDir . $file);
        }

        // Upload the new file
        $fileName = basename($_FILES["uploaded_file"]["name"]);
        $targetFile = $targetDir . $fileName;

        if (move_uploaded_file($_FILES["uploaded_file"]["tmp_name"], $targetFile)) {
            echo "<p>File uploaded successfully for <strong>$area</strong>. Old file replaced if it existed.</p>";
        } else {
            echo "<p style='color:red;'>File upload failed.</p>";
        }

        // Redirect back to avoid resubmission
        echo "<script>window.location='index.php?coop=" . urlencode($_POST['coop']) . "';</script>";
        exit;
    }
    ?>

    <hr>
    <a href="view2.php">View All Files</a>
</body>
</html>