<?php declare(strict_types = 1);

/**
* Generate a series of mysqldump commands with the relevant options.
* Allows you to take a H.U.G.E MySQLdump into smaller files that can be scripted chunk by chunk
* Each table will have its own set of dump files, split into pieces based on the row limit
*/

/**
 * Class ObHelper
 * Helper for output Buffer with a js callback
 */
class ObHelper
{
    public static function callBack ($buffer): void
    {
        echo '<script>' . $buffer . '</script>';
        ob_flush();
        flush();
    }
}

header('Content-Type:text/html;charset=utf-8');
$iStart = microtime(true);

ObHelper::callBack('var oList=parent.document.getElementById("ProcessResults"),oSpinner=parent.document.getElementById("Spinner");');

$aFilterOpts = [
    'DbHost' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_VALIDATE_IP
    ],
    'HostUser' => FILTER_SANITIZE_STRING,
    'HostPass' => FILTER_SANITIZE_STRING,
    'HostDelay' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_SCALAR
    ],
    'DbName' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_REQUIRE_ARRAY
    ],
    'DbTables' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_REQUIRE_ARRAY
    ],
    'DbGuest' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_VALIDATE_IP
    ],
    'GuestUser' => FILTER_SANITIZE_STRING,
    'GuestPass' => FILTER_SANITIZE_STRING,
    'GuestDelay' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_SCALAR
    ],
    'OutputFolder' => FILTER_SANITIZE_STRING,
    'FileType' => FILTER_SANITIZE_STRING,
    'RowSplit' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_SCALAR
    ],
    'LockTable' => FILTER_VALIDATE_INT,
    'RowLimit' => [
        'filter' => FILTER_VALIDATE_INT,
        'flags' => FILTER_REQUIRE_SCALAR
    ],
    'Gzip' => FILTER_VALIDATE_INT,
];
$oOpt = (object)filter_input_array(INPUT_POST, $aFilterOpts);

// If no folder is supplied, use current working directory
$oOpt->OutputFolder = !empty($oOpt->OutputFolder) ? str_replace('\\', '/', realpath($oOpt->OutputFolder)) : '';
if (
    !is_dir($oOpt->OutputFolder) ||
    !is_writeable($oOpt->OutputFolder)
) {
    ObHelper::callBack('oList.innerHTML +="Invalid OutputFolder: ' . $oOpt->OutputFolder . '<br />";');
    ObHelper::callBack('oSpinner.style.visibility="hidden";');
    throw new Exception('Invalid Output Folder');
}
@mkdir($oOpt->OutputFolder . '/sql/', 777, true);

// Set Defaults
$oOpt->RowSplit = $oOpt->RowSplit ?? 1000;
$oOpt->RowLimit = $oOpt->RowLimit ?? 1000;
$oOpt->FileType = $oOpt->FileType ?? 'bat';
$oOpt->HostDelay = !empty($oOpt->HostDelay) ? (($oOpt->FileType=='bat') ? "\ntimeout " . $oOpt->HostDelay : "\nsleep " . $oOpt->HostDelay) : '';
$oOpt->GuestDelay = !empty($oOpt->GuestDelay) ? (($oOpt->FileType=='bat') ? "\ntimeout " . $oOpt->GuestDelay : "\nsleep " . $oOpt->GuestDelay) : '';
if (!empty($oOpt->RowLimit) && !empty($oOpt->RowSplit)) {
    if ($oOpt->RowSplit>$oOpt->RowLimit) {
        ObHelper::callBack('oList.innerHTML +="Invalid Row option combination, RowSplit must be less than RowLimit<br />";');
        ObHelper::callBack('oSpinner.style.visibility="hidden";');
        throw new Exception('Invalid Row option combination, RowSplit must be less than RowLimit');
    }
}

try {
    $oConn = new PDO('mysql:host=' . $oOpt->DbHost . ';dbname=information_schema', $oOpt->HostUser, (!empty($oOpt->HostPass) ? $oOpt->HostPass : ''));
    ObHelper::callBack('oList.innerHTML+="Connected to Host<br />";');
    ObHelper::callBack('oList.innerHTML+="Fetching metadata<br />";');
} catch (Exception $e) {
    ObHelper::callBack('oList.innerHTML+="Error connecting to the Host DB :(<br />";');
    ObHelper::callBack('oList.innerHTML+="' . $e->getMessage() . '(<br />";');
    ObHelper::callBack('oSpinner.style.visibility="hidden";');
    throw new Exception('Error connecting to the Host DB');
}

$fileName = $oOpt->OutputFolder . '/dump_commands.' . $oOpt->FileType;
$fileNameR = $oOpt->OutputFolder . '/restore_commands.' . $oOpt->FileType;
$oFile = fopen($fileName, 'w+');
$oFileR = fopen($fileNameR, 'w+');

if (!empty($oOpt->DbName)) {
    if (is_string($oOpt->DbName)) {
        $oOpt->DbName = explode(',', $oOpt->DbName);
    } else {
        $aTemp = [];
        foreach ($oOpt->DbName as $n => $v) {
            if (strpos($v, ',')!==false) {
                $aTemp = array_merge($aTemp, explode(',', $v));
            } else {
                $aTemp[] = $v;
            }
        }
        $oOpt->DbName = array_unique($aTemp);
    }
}
ObHelper::callBack('oList.innerHTML+="Database:' . implode(',', $oOpt->DbName) . '<br />";');
ObHelper::callBack('oList.innerHTML+="Creating files<br />";');

// Fatch table info from the info schema
$aSchemata = [];
$aDatabases = [];

$aSQL = ['SELECT table_schema `Database`, table_name `Table`, table_rows `Rows` FROM information_schema.tables'];
if (!empty($oOpt->DbTables)) {
    $aSQL[] = "WHERE CONCAT(table_schema, '.', table_name) IN (?)";
    $oStm = $oConn->prepare(implode(' ', $aSQL));
    $oStm->execute([implode("','", $oOpt->DbTables)]);
} else if (!empty($oOpt->DbName)) {
    $aSQL[] = 'WHERE table_schema IN (?)';
    echo implode(' ', $aSQL);
    $oStm = $oConn->prepare(implode(' ', $aSQL));
    $oStm->execute([implode("','", $oOpt->DbName)]);
}

foreach ($oStm->fetchAll(PDO::FETCH_OBJ) as $oSchemata) {
    $aSchemata[$oSchemata->Database][$oSchemata->Table] = $oSchemata->Rows;
    $aDatabases[] = $oSchemata->Database;
}
$aDatabases = array_unique($aDatabases);

// Add the password argument if given
$oOpt->HostPass  = !empty($oOpt->HostPass) ? ' --password=' . $oOpt->HostPass : '';
$oOpt->GuestPass = !empty($oOpt->GuestPass) ? ' --password=' . $oOpt->GuestPass : '';
$echo = ($oOpt->FileType=='bat') ? '@Echo' : 'echo';
$newLine = ($oOpt->FileType=='bat') ? "@Echo:\n" : "\n";
$iSeries = 0;

// File Headers
if ($oOpt->FileType!='bat') {
    fwrite($oFile, "#!/bin/bash\n");
    fwrite($oFileR, "#!/bin/bash\n");
}
fwrite($oFile, "$echo " . (($oOpt->FileType=='bat') ? ' Off' : '') . "\n$newLine\n$echo Multiple MySQLdump script maker\n$newLine\n$echo Created by Qiniso S. Mdletshe \"<QinisoMdletsh@gmail.com>\"\n$newLine");
fwrite($oFile, "$echo Creating dump files...\n");
fwrite($oFile, "\nmysqldump --column-statistics=0 --single-transaction --port=3306 --host=" . $oOpt->DbHost . ' --user=' . $oOpt->HostUser . $oOpt->HostPass . ' --databases --no-create-db ' . implode(' ', $aDatabases) . ' --no-data > ' . '"' . $oOpt->OutputFolder . "/sql/sql_dump_" . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql"');

fwrite($oFileR, "$echo " . (($oOpt->FileType=='bat') ? ' Off' : '') . "\n$newLine\n$echo Multiple MySQL restore script maker\n$newLine\n$echo Created by Qiniso S. Mdletshe \"<QinisoMdletsh@gmail.com>\"\n$newLine");
fwrite($oFileR, "$echo Restoring from files...\n");
fwrite($oFileR, "\nmysql --host=" . $oOpt->DbGuest . ' --user=' . $oOpt->GuestUser . $oOpt->GuestPass . ' < "' . $oOpt->OutputFolder . '/sql/sql_dump_' . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql"');

$iSeries++;

// Loop through DBs and Tables, and split if necessary
foreach ($aSchemata as $dbName => $aTables) {
    $sCommandDump = "\nmysqldump --column-statistics=0 --single-transaction --port=3306 --host=" . $oOpt->DbHost . ' --user=' . $oOpt->HostUser . $oOpt->HostPass . ' ' . $dbName . (isset($oOpt->LockTables) ? ' --lock-tables' : '') . ' --extended-insert --disable-keys --tables ';
    if (isset($oOpt->Gzip)) {
        $sCommandRestore = "\ngunzip < " . '"' . $oOpt->OutputFolder . '/sql/sql_dump_{num}.sql.gz" | mysql --host=' . $oOpt->DbGuest . ' --user=' . $oOpt->GuestUser . $oOpt->GuestPass . ' ' . $dbName;
    } else {
        $sCommandRestore = "\nmysql --host=" . $oOpt->DbGuest . ' --user=' . $oOpt->GuestUser . $oOpt->GuestPass . ' ' . $dbName . ' < "' . $oOpt->OutputFolder . '/sql/sql_dump_{num}.sql"';
    }

    $tableCreateInfo = '';
    foreach ($aTables as $sTableName => $iRows) {
        $iTableBatchCount = ($oOpt->RowLimit>0 && $oOpt->RowLimit > $iRows) ? 1 : ceil($iRows/$oOpt->RowSplit);
        if ($iTableBatchCount<2) {
            if (!empty($oOpt->Gzip)) {
                fwrite($oFile, $sCommandDump . $tableCreateInfo . $sTableName . ' | gzip -9 > "' . $oOpt->OutputFolder . '/sql/sql_dump_' . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql.gz"');
                fwrite($oFileR, str_replace('{num}', str_pad("$iSeries", 10, '0', STR_PAD_LEFT), $sCommandRestore));
            } else {
                fwrite($oFile, $sCommandDump . $tableCreateInfo . $sTableName . ' > "' . $oOpt->OutputFolder . '/sql/sql_dump_' . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql"');
                fwrite($oFileR, str_replace('{num}', str_pad("$iSeries", 10, '0', STR_PAD_LEFT), $sCommandRestore));
            }

            if (!empty($oOpt->HostDelay)) {
                fwrite($oFile, $oOpt->HostDelay);
            }
            if (!empty($oOpt->GuestDelay)) {
                fwrite($oFileR, $oOpt->GuestDelay);
            }
        } else {
            $iOffSet = 0;
            for ($i=1; $i<$iTableBatchCount; $i++) {
                if (isset($oOpt->Gzip)) {
                    fwrite($oFile, $sCommandDump . $tableCreateInfo . $sTableName . ' --opt -w "1 LIMIT ' . $oOpt->RowSplit . ' OFFSET ' . $iOffSet . '" | gzip -9 > "' . $oOpt->OutputFolder . '/sql/sql_dump_' . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql.gz"');
                    fwrite($oFileR, str_replace('{num}', str_pad("$iSeries", 10, '0', STR_PAD_LEFT), $sCommandRestore));
                } else {
                    fwrite($oFile, $sCommandDump . $tableCreateInfo . $sTableName . ' --opt -w "1 LIMIT ' . $oOpt->RowSplit . ' OFFSET ' . $iOffSet . '" > "' . $oOpt->OutputFolder . '/sql/sql_dump_' . str_pad("$iSeries", 10, '0', STR_PAD_LEFT) . '.sql"');
                    fwrite($oFileR, str_replace('{num}', str_pad("$iSeries", 10, '0', STR_PAD_LEFT), $sCommandRestore));
                }

                if (!empty($oOpt->HostDelay)) {
                    fwrite($oFile, $oOpt->HostDelay);
                }
                if (!empty($oOpt->GuestDelay)) {
                    fwrite($oFileR, $oOpt->HostDelay);
                }
                $iOffSet+= $oOpt->RowSplit;
                if ($oOpt->RowLimit>0 && $iOffSet>=$oOpt->RowLimit) {
                    break;
                }
                $iSeries++;
                $tableCreateInfo = ' --no-create-info ';
            }
        }
        $iSeries++;
    }
}

fwrite($oFile, "\n$echo done\n");
fwrite($oFileR, "\n$echo done\n");
fclose($oFile);
fclose($oFileR);
chmod($fileName, 0755);
chmod($fileNameR, 0755);

ObHelper::callBack('oList.innerHTML+="Completed in ' . number_format(microtime(true)-$iStart, 2) . ' seconds<br />";');
ObHelper::callBack('oSpinner.style.visibility="hidden";parent.document.getElementById("progressModal").style.cursor="default";');
