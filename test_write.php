<?php
$testFile = 'qrcodes/test_file.txt';

if (file_put_contents($testFile, 'This is a test file to check write permissions.')) {
    echo "Success! PHP can write to the directory. Test file created at: $testFile";
} else {
    echo "Error: PHP cannot write to the directory. Check folder permissions.";
}
?>
