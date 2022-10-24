<?php
/**
 * Handle upload of SQL and process
 */

if (!isset($_POST['secret']) || $_POST['secret'] !== 'hoOUkjsb*(5bKJGFKA385fg42') {
    header('HTTP/1.0 403 Forbidden');
    echo 'Forbidden';
    exit(0);
}

if (isset($_FILES['sql']) && $_FILES['sql']['error'] == 0) {
    /**
     * Grab the config
     */
    include('config.php');

    /**
     * Connect to database
     */
    $pdo = new \PDO(
        'mysql:host='.$dbHost.';port=3306;dbname='.$dbName.';charset=utf8',
        $dbUser,
        $dbPass,
        []
    );

    echo 'Result: ' . importSqlFile($pdo, $_FILES['sql']['tmp_name']);
} else {
    echo 'File not found';
}

/**
 * Import SQL File
 *
 * @param $pdo
 * @param $sqlFile
 * @param null $tablePrefix
 * @param null $InFilePath
 * @return bool
 */
function importSqlFile($pdo, $sqlFile, $tablePrefix = null, $InFilePath = null)
{
    try {
        // Enable LOAD LOCAL INFILE
        $pdo->setAttribute(\PDO::MYSQL_ATTR_LOCAL_INFILE, true);
        
        $errorDetect = false;
        
        // Temporary variable, used to store current query
        $tmpLine = '';
        
        // Read in entire file
        $lines = file($sqlFile);
        
        // Loop through each line
        foreach ($lines as $line) {
            // Skip it if it's a comment
            if (substr($line, 0, 2) == '--' || trim($line) == '') {
                continue;
            }
            
            // Read & replace prefix
            $line = str_replace(['<<prefix>>', '<<InFilePath>>'], [$tablePrefix, $InFilePath], $line);
            
            // Add this line to the current segment
            $tmpLine .= $line;
            
            // If it has a semicolon at the end, it's the end of the query
            if (substr(trim($line), -1, 1) == ';') {
                try {
                    // Perform the Query
                    $pdo->exec($tmpLine);
                } catch (\PDOException $e) {
                    echo "<br><pre>Error performing Query: '<strong>" . $tmpLine . "</strong>': " . $e->getMessage() . "</pre>\n";
                    $errorDetect = true;
                }
                
                // Reset temp variable to empty
                $tmpLine = '';
            }
        }
        
        // Check if error is detected
        if ($errorDetect) {
            return false;
        }
    } catch (\Exception $e) {
        echo "<br><pre>Exception => " . $e->getMessage() . "</pre>\n";
        return false;
    }
    
    return true;
}
