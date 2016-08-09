<?php
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
	// Fatch table info from the info schema
	$aDatabases = [];
	foreach ($oConn->query("
		SELECT table_schema `Database`
		FROM information_schema.tables
		WHERE table_schema" .
		(!empty($oOpt->DbName)
			? " IN ('" . implode("','", $oOpt->DbName) . "')"
			: " NOT IN ('information_schema','performance_schema','mysql','util_replication')"
		), PDO::FETCH_OBJ) as $oSchemata
	) {
		$aDatabases[] = $oSchemata->Database;
	}
	$aDatabases = array_unique($aDatabases);
	echo json_encode(array_values($aDatabases));
}
