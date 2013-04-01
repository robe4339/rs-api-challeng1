<?php
// (c)2012 Rackspace Hosting
// See COPYING for licensing information

namespace OpenCloud;

require_once('rackspace.php');
require_once('compute.php');

define('AUTHURL', RACKSPACE_US);
define('USERNAME', $_ENV['OS_USERNAME']);
define('TENANT', $_ENV['OS_TENANT_NAME']);
define('APIKEY', $_ENV['NOVA_API_KEY']);


/**
 * numbers each step
 */
function step($msg,$p1=NULL,$p2=NULL,$p3=NULL) {
    global $STEPCOUNTER;
    printf("\nStep %d. %s\n", ++$STEPCOUNTER, sprintf($msg,$p1,$p2,$p3));
}

function info($msg,$p1=NULL,$p2=NULL,$p3=NULL) {
    printf("  %s\n", sprintf($msg,$p1,$p2,$p3));
}

function dot($server) {
	printf("%s %3d%%\n", $server->status, $server->progress);
}

define('TIMEFORMAT', 'r');


// establish our credentials
step('Authenticate');
$connection = new Rackspace(AUTHURL,
	array( 'username' => USERNAME,
		   'apiKey' => APIKEY ));

step('Connect to Cloud Servers');
// now, connect to the compute service
$compute = $connection->Compute('cloudServersOpenStack', 'DFW');

/**
 * Let's build a server. We want to have an OS of CentOS 6.0 or higher and a
 * Flavor with at least 512 of RAM
 */

step('Create 3 servers ');
$list = $compute->ImageList(TRUE, array('name'=>'CentOS 6.3'));
$image = $list->First();
$flavor = $compute->Flavor(2); // 512MB

// Name the servers
$servername = "Web";

//We're going to make 3 servers
$amountofservers = '3';

$i=0;
for($i=1; $i <= $amountofservers; $i++){
// let's create the servers
info("Creating Server $servername$i");
$server = $compute->Server();
$server->Create(array(
		'name' => "$servername$i",
		'image' => $image,
		'flavor' => $flavor));
print("requested, now waiting...\n");
#print("ID=".$server->id."...\n");

$server->WaitFor("ACTIVE", 900, 'OpenCloud\dot');
printf("ID is %s, root password is %s, IP Adress is %s\n", $server->id, $server->adminPass, $server->ip());
}

print("done\n");
exit(0);


