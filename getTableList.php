<?php declare(strict_types = 1);
header('Content-Type:application/json');
$aFilterOpts = [
    'DbHost' => [
        'filter' => FILTER_SANITIZE_STRING,
        'flags' => FILTER_VALIDATE_IP
    ],
    'HostUser' => FILTER_SANITIZE_STRING,
    'HostPass' => FILTER_SANITIZE_STRING,
];
$aInput = filter_input_array(INPUT_POST, $aFilterOpts);
$oOpt = (object)$aInput;

try{
    $oConn = new PDO('mysql:host=' . $oOpt->DbHost . ';dbname=information_schema', $oOpt->HostUser, (!empty($oOpt->HostPass) ? $oOpt->HostPass : ''));
} catch (Exception $e) {
    echo json_encode(['Error' => 'Could not connect to database']);
}

if ($oConn) {
    // Fetch table info from the info schema
    $aTables = [];
    $oStm = $oConn->prepare("
        SELECT CONCAT(table_schema, '.', table_name)  `Table`
        FROM information_schema.tables
        WHERE table_schema" .
        (!empty($oOpt->DbName)
            ? " IN (?)"
            : " NOT IN ('information_schema','performance_schema','mysql','util_replication')"
        )
    );

    if (!empty($oOpt->DbName)) {
        $oStm->execute([implode("','", $oOpt->DbName)]);
    } else {
        $oStm->execute();
    }

    foreach ($oStm->fetchAll(PDO::FETCH_OBJ) as $oSchemata) {
        $aTables[] = $oSchemata->Table;
    }
    $aTables = array_unique($aTables);
    echo json_encode(array_values($aTables));
}
